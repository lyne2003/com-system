<?php

namespace App\Services;

class SupplierTop4Service
{
    /**
     * Compute "Supplier Top 4" based on the Excel formula:
     *
     * =IFERROR(INDEX(FILTER(AJ2:AV2, (AJ2:AV2<>AE2)*(AJ2:AV2<>AF2)*(AJ2:AV2<>AG2)), 1), "")
     *
     * Where:
     *   AJ2:AV2 = all suppliers (S1–S5, Brand S1–S4, Subcat S1–S4)
     *   AE2 = supplier_top1
     *   AF2 = supplier_top2
     *   AG2 = supplier_top3
     *
     * Logic:
     *   From the full supplier list (left to right), exclude Top1, Top2, Top3,
     *   and return the first non-empty remaining supplier.
     *   If none found, return "".
     *
     * @param array       $allSuppliers  [S1..S5, BrandS1..S4, SubcatS1..S4]
     * @param string|null $supplierTop1
     * @param string|null $supplierTop2
     * @param string|null $supplierTop3
     * @return string
     */
    public static function resolve(
        array   $allSuppliers,
        ?string $supplierTop1,
        ?string $supplierTop2,
        ?string $supplierTop3
    ): string {
        $excluded = array_filter(
            [trim($supplierTop1 ?? ''), trim($supplierTop2 ?? ''), trim($supplierTop3 ?? '')],
            fn($x) => $x !== ''
        );

        foreach ($allSuppliers as $s) {
            $s = trim($s ?? '');
            if ($s === '' || in_array($s, $excluded, true)) {
                continue;
            }
            return $s;
        }

        return '';
    }
}
