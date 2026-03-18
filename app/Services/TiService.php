<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TiService
{
    protected string $clientId;
    protected string $clientSecret;
    protected ?string $token = null;

    public function __construct()
    {
        $this->clientId     = config('services.ti.client_id');
        $this->clientSecret = config('services.ti.client_secret');
    }

    protected function getToken(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        $response = Http::timeout(15)
            ->asForm()
            ->post('https://transact.ti.com/v1/oauth/accesstoken', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

        $this->token = $response->json('access_token');
        return $this->token;
    }

    public function search(string $partNumber, int $quantity = 1): array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                return $this->error($partNumber, 'Failed to get TI token');
            }

            $encoded = rawurlencode(trim($partNumber));

            // Step 1: Get product info
            $productResponse = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $token])
                ->get("https://transact.ti.com/v1/products-extended/{$encoded}?page=0");

            if ($productResponse->status() === 404) {
                return $this->notFound($partNumber, $productResponse->json() ?? []);
            }

            if ($productResponse->status() !== 200) {
                return $this->notFound($partNumber, $productResponse->json() ?? []);
            }

            $productData = $productResponse->json();
            $product     = $productData['Product'] ?? null;

            if (!$product) {
                return $this->notFound($partNumber, $productData);
            }

            // Step 2: Get pricing
            $pricingResponse = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'accept'        => 'application/json',
                ])
                ->get("https://transact.ti.com/v2/store/products/{$encoded}?currency=USD&exclude-evms=true");

            $pricingData  = $pricingResponse->json() ?? [];
            $availableQty = $pricingData['quantity'] ?? 0;
            $effectiveQty = ($quantity > 0) ? min($quantity, $availableQty) : $availableQty;

            // Find best price break
            $price = null;
            foreach ($pricingData['pricing'][0]['priceBreaks'] ?? [] as $pb) {
                if ($effectiveQty >= $pb['priceBreakQuantity']) {
                    $price = $pb['price'];
                }
            }

            // Package info
            $packageInfo = null;
            if (!empty($product['FullBoxQty']) && !empty($product['PackageCarrier'])) {
                $packageInfo = $product['FullBoxQty'] . ' | ' . $product['PackageCarrier'];
            }

            $packagePins = null;
            if (!empty($product['PackageGroup']) && !empty($product['PackageType'])) {
                $packagePins = $product['PackageGroup'] . ' (' . $product['PackageType'] . ')';
                if (!empty($product['Pin'])) {
                    $packagePins .= ' | ' . $product['Pin'];
                }
            }

            $leadTime = null;
            if (!empty($product['LeadTimeWeeks'])) {
                $leadTime = $product['LeadTimeWeeks'] . ' weeks';
            }

            return [
                'status'         => $price !== null ? 'found' : ($availableQty > 0 ? 'found' : 'no_stock'),
                'supplier'       => 'ti',
                'partnumber'     => $partNumber,
                'description'    => $product['Description'] ?? null,
                'manufacturer'   => 'Texas Instruments',
                'manufacturer_pn'=> $product['Identifier'] ?? null,
                'unit_price'     => $price !== null ? (float) $price : null,
                'availability'   => $availableQty,
                'stock_status'   => $product['LifeCycleStatus'] ?? null,
                'lead_time'      => $leadTime,
                'moq'            => null,
                'package_type'   => $packagePins,
                'package_qty'    => !empty($product['FullBoxQty']) ? (int) $product['FullBoxQty'] : null,
                'datasheet_url'  => $product['DatasheetUrl'] ?? null,
                'category'       => null,
                'raw_response'   => json_encode(['product' => $productData, 'pricing' => $pricingData]),
            ];

        } catch (\Throwable $e) {
            Log::error('TiService error: ' . $e->getMessage());
            return $this->error($partNumber, $e->getMessage());
        }
    }

    private function notFound(string $partNumber, array $raw): array
    {
        return [
            'status'       => 'not_found',
            'supplier'     => 'ti',
            'partnumber'   => $partNumber,
            'raw_response' => json_encode($raw),
        ];
    }

    private function error(string $partNumber, string $message): array
    {
        return [
            'status'       => 'error',
            'supplier'     => 'ti',
            'partnumber'   => $partNumber,
            'raw_response' => $message,
        ];
    }
}
