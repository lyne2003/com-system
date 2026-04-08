<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MouserService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.mouser.com/api/v1/search/partnumber';

    public function __construct()
    {
        $this->apiKey = config('services.mouser.api_key');
    }

    /**
     * Search for a part number and return normalized result.
     */
    public function search(string $partNumber, int $quantity = 1): array
    {
        try {
            $response = Http::timeout(15)
                ->post($this->apiUrl . '?apiKey=' . $this->apiKey, [
                    'SearchByPartRequest' => [
                        'mouserPartNumber'   => $partNumber,
                        'partSearchOptions'  => '',
                    ],
                ]);

            if ($response->status() === 403) {
                // Rate limit — retry once after delay
                sleep(2);
                $response = Http::timeout(15)
                    ->post($this->apiUrl . '?apiKey=' . $this->apiKey, [
                        'SearchByPartRequest' => [
                            'mouserPartNumber'   => $partNumber,
                            'partSearchOptions'  => '',
                        ],
                    ]);
            }

            $data  = $response->json();
            $parts = $data['SearchResults']['Parts'] ?? [];

            if (empty($parts)) {
                return $this->notFound($partNumber, $data);
            }

            // Build product dictionary
            $productDict    = [];
            $removedProducts = [];

            foreach ($parts as $part) {
                $stock = (int) ($part['AvailabilityInStock'] ?? 0);
                $avail = $part['Availability'] ?? '';

                if ($stock <= 0 && !str_contains($avail, 'In Stock')) {
                    $removedProducts[] = $part;
                } else {
                    $productDict[$part['MouserPartNumber']] = $part;
                }
            }

            // Calculate prices for in-stock products
            foreach ($productDict as $key => $part) {
                $availableQty = 0;
                if (isset($part['Availability']) && str_contains($part['Availability'], 'In Stock')) {
                    preg_match('/^[\d,]+/', $part['Availability'], $matches);
                    if (!empty($matches[0])) {
                        $availableQty = (int) str_replace(',', '', $matches[0]);
                    }
                }

                $finalQty        = min($availableQty, $quantity);
                $lowestPrice     = PHP_FLOAT_MAX;
                $opportunityPrice = PHP_FLOAT_MAX;

                foreach ($part['PriceBreaks'] ?? [] as $pb) {
                    $price = (float) preg_replace('/[$,]/', '', $pb['Price'] ?? '0');
                    if ($pb['Quantity'] <= $finalQty && $price < $lowestPrice) {
                        $lowestPrice = $price;
                    }
                    if ($pb['Quantity'] <= $quantity) {
                        $opportunityPrice = $price;
                    }
                }

                $productDict[$key]['_SelectedPrice']    = $lowestPrice === PHP_FLOAT_MAX ? null : $lowestPrice;
                $productDict[$key]['_OpportunityPrice'] = $opportunityPrice === PHP_FLOAT_MAX ? null : $opportunityPrice;
                $productDict[$key]['_AvailableQty']     = $availableQty;
            }

            // Try exact MPN match first
            $inputMpn  = strtoupper(trim($partNumber));
            $bestPart  = null;

            foreach ($productDict as $part) {
                $mpn         = strtoupper(trim($part['ManufacturerPartNumber'] ?? ''));
                $stock       = (int) ($part['AvailabilityInStock'] ?? 0);
                $restriction = strtolower($part['RestrictionMessage'] ?? '');

                if ($mpn === $inputMpn && $stock > 0 && !str_contains($restriction, 'not presently sell')) {
                    $bestPart = $part;
                    break;
                }
            }

            // Fall back to lowest price
            if (!$bestPart) {
                $bestPrice = PHP_FLOAT_MAX;
                foreach ($productDict as $part) {
                    if (($part['_SelectedPrice'] ?? PHP_FLOAT_MAX) < $bestPrice) {
                        $bestPrice = $part['_SelectedPrice'];
                        $bestPart  = $part;
                    }
                }
            }

            // If still nothing, use first removed (no stock)
            if (!$bestPart && !empty($removedProducts)) {
                return $this->buildNoStock($partNumber, $removedProducts[0], $data);
            }

            if (!$bestPart) {
                return $this->notFound($partNumber, $data);
            }

            return $this->buildResult($partNumber, $bestPart, $quantity, $data);

        } catch (\Throwable $e) {
            Log::error('MouserService error: ' . $e->getMessage());
            return [
                'status'       => 'error',
                'partnumber'   => $partNumber,
                'supplier'     => 'mouser',
                'raw_response' => $e->getMessage(),
            ];
        }
    }

    private function buildResult(string $partNumber, array $part, int $quantity, array $raw): array
    {
        $availableQty = $part['_AvailableQty'] ?? 0;
        $price        = $part['_SelectedPrice'] ?? null;

        // Lead time: if not enough stock → "2-4 weeks", else convert days to weeks
        if ($quantity > $availableQty) {
            $leadTime = '2-4 weeks';
        } else {
            $lt = $part['LeadTime'] ?? null;
            $leadTime = $lt ? (floor((int) preg_replace('/\D/', '', $lt) / 7) . ' weeks') : null;
        }

        $packageType = null;
        $packageQty  = $part['Min'] ?? null;

        foreach ($part['ProductAttributes'] ?? [] as $attr) {
            if ($attr['AttributeName'] === 'Packaging') {
                $val = $attr['AttributeValue'] ?? '';
                if (!str_contains(strtolower($val), 'mousereel')) {
                    $packageType = $val;
                }
            }
            if ($attr['AttributeName'] === 'Standard Pack Qty') {
                $packageQty = $attr['AttributeValue'] ?? $packageQty;
            }
        }

        return [
            'status'         => $price !== null ? 'found' : 'no_stock',
            'supplier'       => 'mouser',
            'partnumber'     => $partNumber,
            'description'    => $part['Description'] ?? null,
            'manufacturer'   => $part['Manufacturer'] ?? null,
            'manufacturer_pn'=> $part['ManufacturerPartNumber'] ?? null,
            'unit_price'     => $price,
            'availability'   => $availableQty,
            'stock_status'   => $part['Availability'] ?? null,
            'lead_time'      => $leadTime,
            'moq'            => is_numeric($part['Min'] ?? null) ? (int) $part['Min'] : null,
            'package_type'   => $packageType,
            'package_qty'    => is_numeric($packageQty) ? (int) $packageQty : null,
            'datasheet_url'  => $part['DataSheetUrl'] ?? null,
            'category'       => $part['Category'] ?? null,
            'raw_response'   => json_encode($raw),
        ];
    }

    private function buildNoStock(string $partNumber, array $part, array $raw): array
    {
        $packageType = null;
        $packageQty  = $part['Min'] ?? null;

        foreach ($part['ProductAttributes'] ?? [] as $attr) {
            if ($attr['AttributeName'] === 'Packaging') {
                $val = $attr['AttributeValue'] ?? '';
                if (!str_contains(strtolower($val), 'mousereel')) {
                    $packageType = $val;
                }
            }
            if ($attr['AttributeName'] === 'Standard Pack Qty') {
                $packageQty = $attr['AttributeValue'] ?? $packageQty;
            }
        }

        $lt = $part['LeadTime'] ?? null;
        $leadTime = $lt ? (floor((int) preg_replace('/\D/', '', $lt) / 7) . ' weeks') : null;

        return [
            'status'         => 'no_stock',
            'supplier'       => 'mouser',
            'partnumber'     => $partNumber,
            'description'    => $part['Description'] ?? null,
            'manufacturer'   => $part['Manufacturer'] ?? null,
            'manufacturer_pn'=> $part['ManufacturerPartNumber'] ?? null,
            'unit_price'     => null,
            'availability'   => 0,
            'stock_status'   => $part['Availability'] ?? 'No Stock',
            'lead_time'      => $leadTime,
            'moq'            => is_numeric($part['Min'] ?? null) ? (int) $part['Min'] : null,
            'package_type'   => $packageType,
            'package_qty'    => is_numeric($packageQty) ? (int) $packageQty : null,
            'datasheet_url'  => $part['DataSheetUrl'] ?? null,
            'category'       => $part['Category'] ?? null,
            'raw_response'   => json_encode($raw),
        ];
    }

    private function notFound(string $partNumber, array $raw): array
    {
        return [
            'status'       => 'not_found',
            'supplier'     => 'mouser',
            'partnumber'   => $partNumber,
            'raw_response' => json_encode($raw),
        ];
    }
}
