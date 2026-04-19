<?php

namespace App\Services;

class SupplierTop2Service
{
    /**
     * Compute "Supplier Top 2" based on the Excel formula:
     *
     * =IF(AB2="Passive","Ordex",
     *    IF(ISNUMBER(SEARCH("Analog",W2)),"Kehuite",
     *       IF(AND(E2<>"",AJ2="",AK2=""),"Kehuite",
     *          IF(AJ2<>AE2, AJ2, AK2)
     *       )
     *    )
     * )
     *
     * Where:
     *   AB2 = component_type  ("Passive" / "Active")
     *   W2  = best_manufacturer
     *   E2  = partnumber (non-empty means the row has a part)
     *   AJ2 = recommended_suppliers[0]  (Supplier 1 based on type)
     *   AK2 = recommended_suppliers[1]  (Supplier 2 based on type)
     *   AE2 = supplier_top1
     *
     * Logic:
     *  1. If type is Passive → "Ordex"
     *  2. Else if manufacturer contains "Analog" → "Kehuite"
     *  3. Else if part number is not empty AND Supplier1 is empty AND Supplier2 is empty → "Kehuite"
     *  4. Else if Supplier Top 1 ≠ Supplier 1 → return Supplier 1
     *  5. Else (Supplier Top 1 = Supplier 1) → return Supplier 2
     *
     * @param string|null $componentType      "Passive" | "Active" | null
     * @param string|null $manufacturer       best_manufacturer
     * @param string|null $partNumber         items.partnumber
     * @param string|null $supplier1          recommended_suppliers[0]
     * @param string|null $supplier2          recommended_suppliers[1]
     * @param string|null $supplierTop1       the already-computed Supplier Top 1
     * @return string
     */
    public static function resolve(
        ?string $componentType,
        ?string $manufacturer,
        ?string $partNumber,
        ?string $supplier1,
        ?string $supplier2,
        ?string $supplierTop1
    ): string {
        $s1   = trim($supplier1   ?? '');
        $s2   = trim($supplier2   ?? '');
        $top1 = trim($supplierTop1 ?? '');
        $mfr  = strtolower(trim($manufacturer ?? ''));
        $pn   = trim($partNumber ?? '');

        // 1. Passive → Ordex
        if ($componentType === 'Passive') {
            return 'Ordex';
        }

        // 2. Manufacturer contains "Analog" → Kehuite
        if (str_contains($mfr, 'analog')) {
            return 'Kehuite';
        }

        // 3. Part number not empty AND Supplier1 empty AND Supplier2 empty → Kehuite
        if ($pn !== '' && $s1 === '' && $s2 === '') {
            return 'Kehuite';
        }

        // 4. Supplier Top 1 ≠ Supplier 1 → return Supplier 1
        if ($top1 !== $s1) {
            return $s1;
        }

        // 5. Supplier Top 1 = Supplier 1 → return Supplier 2
        return $s2;
    }
}
