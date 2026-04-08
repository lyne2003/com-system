<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DigiKeyService
{
    protected string $clientId;
    protected string $clientSecret;
    protected ?string $token = null;

    public function __construct()
    {
        $this->clientId     = config('services.digikey.client_id');
        $this->clientSecret = config('services.digikey.client_secret');
    }

    protected function getToken(): ?string
    {
        // Use Laravel cache to persist token across requests (expires in 55 min)
        $cacheKey = 'digikey_oauth_token_' . md5($this->clientId);
        if ($cached = cache($cacheKey)) {
            $this->token = $cached;
            return $this->token;
        }

        $response = Http::timeout(10)
            ->asForm()
            ->post('https://api.digikey.com/v1/oauth2/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

        $this->token = $response->json('access_token');
        if ($this->token) {
            cache([$cacheKey => $this->token], now()->addMinutes(55));
        }
        return $this->token;
    }

    public function search(string $partNumber, int $quantity = 1): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return $this->error($partNumber, 'Failed to get DigiKey token');
            }

            $encoded  = rawurlencode(trim($partNumber));
            $url      = "https://api.digikey.com/products/v4/search/{$encoded}/productdetails";

            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization'      => 'Bearer ' . $token,
                    'X-DIGIKEY-Client-Id'=> $this->clientId,
                    'Content-Type'       => 'application/json',
                ])
                ->get($url);

            if ($response->status() === 401) {
                // Token expired — clear cache, refresh and retry
                $cacheKey = 'digikey_oauth_token_' . md5($this->clientId);
                cache()->forget($cacheKey);
                $this->token = null;
                $token = $this->getToken();
                $response = Http::timeout(15)
                    ->withHeaders([
                        'Authorization'      => 'Bearer ' . $token,
                        'X-DIGIKEY-Client-Id'=> $this->clientId,
                        'Content-Type'       => 'application/json',
                    ])
                    ->get($url);
            }

            $data = $response->json();

            if ($response->status() !== 200 || empty($data['Product'])) {
                return $this->notFound($partNumber, $data);
            }

            $product = $data['Product'];

            // Build price table from all variations
            $priceTable = [];
            foreach ($product['ProductVariations'] ?? [] as $variation) {
                $packageType      = $variation['PackageType']['Name'] ?? 'N/A';
                $digiKeyPartNumber = $variation['DigiKeyProductNumber'] ?? null;

                foreach ($variation['StandardPricing'] ?? [] as $price) {
                    $priceTable[] = [
                        'BreakQuantity'    => (int) $price['BreakQuantity'],
                        'UnitPrice'        => (float) $price['UnitPrice'],
                        'PackageType'      => $packageType,
                        'DigiKeyPartNumber'=> $digiKeyPartNumber,
                    ];
                }
            }

            if (empty($priceTable)) {
                return $this->notFound($partNumber, $data);
            }

            // Sort by unit price ascending
            usort($priceTable, fn($a, $b) => $a['UnitPrice'] <=> $b['UnitPrice']);

            $lowestPriceItem      = $priceTable[0];
            $lowestPricePartNumber = $lowestPriceItem['DigiKeyPartNumber'];

            // Effective qty = min(requested, available)
            $availableQty = $product['QuantityAvailable'] ?? 0;
            $effectiveQty = min($quantity, $availableQty);

            // Find largest price break ≤ effectiveQty
            $allocation = [];
            $largestAvailable = ['BreakQuantity' => 0, 'UnitPrice' => 0, 'PackageType' => 'N/A', 'DigiKeyPartNumber' => null];

            foreach ($priceTable as $pb) {
                if ($pb['BreakQuantity'] <= $effectiveQty && $pb['BreakQuantity'] > $largestAvailable['BreakQuantity']) {
                    $largestAvailable = $pb;
                }
            }

            if ($largestAvailable['BreakQuantity'] > 0) {
                $allocation[] = $largestAvailable;
            }

            // Enforce Tape & Reel rule
            $hasTapeAndReel = collect($allocation)->contains('PackageType', 'Tape & Reel (TR)');
            if (!$hasTapeAndReel && count($allocation) > 1) {
                $allocation = [$allocation[0]];
            }

            $totalPrice   = array_sum(array_column($allocation, 'UnitPrice'));
            $averagePrice = count($allocation) > 0 ? $totalPrice / count($allocation) : 0;
            $packageType  = $allocation[0]['PackageType'] ?? 'N/A';

            // MOQ
            $moq = count($priceTable) > 0 ? min(array_column($priceTable, 'BreakQuantity')) : null;

            // Stock status
            $stockStatus = 'Out of Stock';
            if ($availableQty > 0) {
                $stockStatus = 'In Stock';
            } elseif (!empty($product['NormallyStocking'])) {
                $stockStatus = 'Normally Stocking';
            } elseif (!empty($product['Discontinued'])) {
                $stockStatus = 'Obsolete';
            }

            $classifications = $product['Classifications'] ?? [];

            return [
                'status'         => $averagePrice > 0 ? 'found' : 'no_stock',
                'supplier'       => 'digikey',
                'partnumber'     => $partNumber,
                'description'    => $product['Description']['ProductDescription'] ?? null,
                'manufacturer'   => $product['Manufacturer']['Name'] ?? null,
                'manufacturer_pn'=> $product['ManufacturerProductNumber'] ?? null,
                'unit_price'     => $averagePrice > 0 ? round($averagePrice, 4) : null,
                'availability'   => $availableQty,
                'stock_status'   => $stockStatus,
                'lead_time'      => $product['ManufacturerLeadWeeks'] ? $product['ManufacturerLeadWeeks'] . ' weeks' : null,
                'moq'            => $moq,
                'package_type'   => $packageType,
                'package_qty'    => $effectiveQty ?: null,
                'datasheet_url'  => $product['DatasheetUrl'] ?? null,
                'category'       => $product['Category']['Name'] ?? null,
                'raw_response'   => json_encode($data),
            ];

        } catch (\Throwable $e) {
            Log::error('DigiKeyService error: ' . $e->getMessage());
            return $this->error($partNumber, $e->getMessage());
        }
    }

    private function notFound(string $partNumber, array $raw): array
    {
        return [
            'status'       => 'not_found',
            'supplier'     => 'digikey',
            'partnumber'   => $partNumber,
            'raw_response' => json_encode($raw),
        ];
    }

    private function error(string $partNumber, string $message): array
    {
        return [
            'status'       => 'error',
            'supplier'     => 'digikey',
            'partnumber'   => $partNumber,
            'raw_response' => $message,
        ];
    }
}
