<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupplierSubcategoryService
{
    /** Cached DB data: supplier => [subcategory => count] */
    private static ?array $dbData = null;

    /**
     * Load data from DB if available.
     */
    private static function getData(): array
    {
        if (self::$dbData !== null) {
            return self::$dbData;
        }

        try {
            if (!Schema::hasTable('supplier_subcategories')) {
                self::$dbData = [];
                return self::$dbData;
            }

            $rows = DB::table('supplier_subcategories')->get();
            if ($rows->isEmpty()) {
                self::$dbData = [];
                return self::$dbData;
            }

            $data = [];
            foreach ($rows as $row) {
                $data[$row->supplier_name][$row->subcategory_name] = $row->count;
            }
            self::$dbData = $data;
        } catch (\Throwable $e) {
            self::$dbData = [];
        }

        return self::$dbData;
    }

    /**
     * Given a subcategory name, return the top N SMO suppliers
     * that carry that subcategory, ranked by count descending.
     *
     * @param string $subcategory  The subcategory name (e.g. "Multilayer Ceramic Capacitors MLCC")
     * @param int    $limit        How many suppliers to return (default 4)
     * @return array               Array of supplier names
     */
    public static function getTopSuppliersForSubcategory(string $subcategory, int $limit = 4): array
    {
        if (empty($subcategory)) {
            return [];
        }

        $subLower = strtolower(trim($subcategory));
        $data = self::getData();

        $scores = [];
        foreach ($data as $supplier => $subcategories) {
            foreach ($subcategories as $s => $count) {
                if (strtolower($s) === $subLower) {
                    $scores[$supplier] = $count;
                    break;
                }
            }
        }

        if (empty($scores)) {
            return [];
        }

        arsort($scores);
        return array_slice(array_keys($scores), 0, $limit);
    }

    /**
     * Invalidate the in-memory cache (call after uploading new data).
     */
    public static function clearCache(): void
    {
        self::$dbData = null;
    }
}
