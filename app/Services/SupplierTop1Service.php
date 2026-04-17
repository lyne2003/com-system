<?php

namespace App\Services;

class SupplierTop1Service
{
    /**
     * Compute the "Supplier Top 1" based on the IFS logic from the Excel formula.
     *
     * @param string|null $manufacturer  The best manufacturer name (RFQ → Mouser → DigiKey → TI)
     * @param float|null  $volume        unit_price × qty  (Mouser → DigiKey → TI)
     * @param array       $allSuppliers  All other supplier columns already computed
     *                                   [S1,S2,S3,S4,S5, BrandS1..S4, SubcatS1..S4]
     * @return string
     */
    public static function resolve(
        ?string $manufacturer,
        ?float  $volume,
        array   $allSuppliers = []
    ): string {
        $m = strtolower(trim($manufacturer ?? ''));
        $v = $volume ?? 0;

        // Helper: case-insensitive substring search
        $has = fn(string $needle) => str_contains($m, strtolower($needle));

        // 1. Asai Kosan
        if (
            $has('Altera')   || $has('Xilinx INC') || $has('Xilinx') ||
            $has('AMD')      || $has('Intel')       || $has('Analog') ||
            $has('Linear')   || $has('ADI')         || $has('Traco')  ||
            $has('Recom')    || $has('Maxim')       || $has('CUI Inc.') ||
            $has('XP Power')
        ) {
            return 'Asai Kosan';
        }

        // 2. Maxtronic
        if (
            $has('Mean well') || $has('Hottech') ||
            $has('Semikron')  || $has('Analogue')
        ) {
            return 'Maxtronic';
        }

        // 3. Shainor  (Yageo/Murata/Degson without volume condition)
        if ($has('Yageo') || $has('Murata') || $has('Degson')) {
            return 'Shainor';
        }

        // 4. Perceptive
        if (
            ($has('KEMET') && $v >= 300) ||
            $has('Connfly') || $has('KLS') ||
            $has('Degson')  || $has('Coilcraft')
        ) {
            return 'Perceptive';
        }

        // 5. ATP  (Samsung or Yageo with volume ≥ 300)
        if (
            ($has('Samsung') && $v >= 300) ||
            ($has('Yageo')   && $v >= 300)
        ) {
            return 'ATP';
        }

        // 6. USIE
        if ($has('TE Connectivity')) {
            return 'USIE';
        }

        // 7. SMYG
        if ($has('Wurth') || $has('Würth')) {
            return 'SMYG';
        }

        // 8. Sourceability
        if ($has('Nvidia')) {
            return 'Sourceability';
        }

        // 9. Kehuite
        if ($has('Texas Instruments')) {
            return 'Kehuite';
        }

        // 10. Hong Kong Ruifan
        if ($has('Nuvoton')) {
            return 'Hong Kong Ruifan Microelectronics Co., Limited';
        }

        // 11. Yuete
        if (
            $has('Nexperia') || $has('Chemi-Con') ||
            $has('Nichicon') || $has('Rubycon')
        ) {
            return 'Yuete';
        }

        // 12. LinkIC
        if ($has('Vicor') || $has('Semikron')) {
            return 'LinkIC';
        }

        // 13. TRUE fallback: last non-empty supplier from all supplier columns
        foreach (array_reverse($allSuppliers) as $s) {
            if (!empty($s) && $s !== '—') {
                return $s;
            }
        }

        // If nothing at all, return first supplier
        return $allSuppliers[0] ?? '';
    }
}
