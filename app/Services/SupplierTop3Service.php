<?php

namespace App\Services;

class SupplierTop3Service
{
    /**
     * Compute "Supplier Top 3" based on the Excel formula:
     *
     * =IF(AND(AB2="Passive",V2>1000),"Ariat",
     *    IF(AND(E2<>"",AJ2="",AK2=""),"LinkIC",
     *       IFERROR(
     *         INDEX(AJ2:AN2, MODE(IF(AJ2:AN2<>"", IF(COUNTIF(AE2:AF2,AJ2:AV2)=0, MATCH(AJ2:AN2,AJ2:AN2,0))))),
     *         IF(AL2=AE2,"",AL2)
     *       )
     *    )
     * )
     *
     * Where:
     *   AB2 = component_type
     *   V2  = volume (unit_price × qty)
     *   E2  = partnumber
     *   AJ2 = recommended_suppliers[0]  (Supplier 1 based on type)
     *   AK2 = recommended_suppliers[1]  (Supplier 2 based on type)
     *   AL2 = recommended_suppliers[2]  (Supplier 3 based on type)
     *   AE2 = supplier_top1
     *   AF2 = supplier_top2
     *   AJ2:AV2 = all suppliers (S1–S5, Brand S1–S4, Subcat S1–S4)
     *
     * Logic:
     *  1. If Passive AND volume > 1000 → "Ariat"
     *  2. Else if part number not empty AND type-Supplier1 empty AND type-Supplier2 empty → "LinkIC"
     *  3. Else: from the full supplier list, exclude Top1 and Top2, return most frequent remaining.
     *     Fallback: if type-Supplier3 equals Top1, return empty; otherwise return type-Supplier3.
     *
     * @param string|null $componentType
     * @param float|null  $volume
     * @param string|null $partNumber
     * @param array       $recommendedSuppliers  Type-based suppliers [S1,S2,S3,S4,S5]
     * @param array       $allSuppliers          Full merged list [S1..S5, BrandS1..S4, SubcatS1..S4]
     * @param string|null $supplierTop1
     * @param string|null $supplierTop2
     * @return string
     */
    public static function resolve(
        ?string $componentType,
        ?float  $volume,
        ?string $partNumber,
        array   $recommendedSuppliers,
        array   $allSuppliers,
        ?string $supplierTop1,
        ?string $supplierTop2
    ): string {
        $v    = $volume ?? 0;
        $pn   = trim($partNumber ?? '');
        $top1 = trim($supplierTop1 ?? '');
        $top2 = trim($supplierTop2 ?? '');

        // Type-based Supplier 1, 2, 3 (AJ, AK, AL in Excel)
        $s1 = trim($recommendedSuppliers[0] ?? '');
        $s2 = trim($recommendedSuppliers[1] ?? '');
        $s3 = trim($recommendedSuppliers[2] ?? '');

        // 1. Passive AND volume > 1000 → Ariat
        if ($componentType === 'Passive' && $v > 1000) {
            return 'Ariat';
        }

        // 2. Part number not empty AND type-Supplier1 empty AND type-Supplier2 empty → LinkIC
        if ($pn !== '' && $s1 === '' && $s2 === '') {
            return 'LinkIC';
        }

        // 3. Most frequent supplier from the full list, excluding Top1 and Top2
        $excluded = array_filter([$top1, $top2], fn($x) => $x !== '');

        $counts = [];
        foreach ($allSuppliers as $s) {
            $s = trim($s ?? '');
            if ($s === '' || in_array($s, $excluded, true)) {
                continue;
            }
            $counts[$s] = ($counts[$s] ?? 0) + 1;
        }

        if (!empty($counts)) {
            arsort($counts);
            return array_key_first($counts);
        }

        // IFERROR fallback: if type-Supplier3 equals Top1, return empty; otherwise return type-Supplier3
        if ($s3 === $top1) {
            return '';
        }

        return $s3;
    }
}
