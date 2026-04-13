<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InquiryNumberService
{
    /**
     * Countries mapped to region groups (case-insensitive matching).
     */
    protected array $countryGroups = [
        'africa' => ['egypt', 'tunisia', 'algeria'],
        'gcc'    => ['united arab emirates', 'uae', 'kuwait', 'qatar', 'oman', 'bahrain'],
    ];

    /**
     * Base (starting) number for each region group.
     * The sequence resets to base+1 every Monday.
     */
    protected array $baseNumbers = [
        'africa' => 10000,
        'gcc'    => 20000,
    ];

    /**
     * Get the Monday date of the current week (as a date string YYYY-MM-DD).
     */
    public function currentWeekStart(): string
    {
        return Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    /**
     * Determine the region group for a given company ID.
     * Returns 'africa', 'gcc', or null if not matched.
     */
    public function getRegionGroup(string|int $companyId): ?string
    {
        $company = DB::table('companies')
            ->leftJoin('countries', 'companies.country_id', '=', 'countries.id')
            ->select('countries.name as country_name')
            ->where('companies.id', $companyId)
            ->first();

        if (!$company || empty($company->country_name)) {
            return null;
        }

        $countryLower = strtolower(trim($company->country_name));

        foreach ($this->countryGroups as $group => $countries) {
            if (in_array($countryLower, $countries, true)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Peek at the next inquiry number for a company (without incrementing).
     * Returns null if the company's country is not in a known region group.
     */
    public function peekNextNumber(string|int $companyId): ?int
    {
        $group = $this->getRegionGroup($companyId);
        if (!$group) {
            return null;
        }

        $weekStart = $this->currentWeekStart();
        $base      = $this->baseNumbers[$group];

        $row = DB::table('inquiry_sequences')
            ->where('region_group', $group)
            ->where('week_start', $weekStart)
            ->first();

        return $row ? ($row->last_number + 1) : ($base + 1);
    }

    /**
     * Atomically assign and return the next inquiry number for a company.
     * Increments the counter in the DB. Returns null if region not matched.
     */
    public function assignNextNumber(string|int $companyId): ?int
    {
        $group = $this->getRegionGroup($companyId);
        if (!$group) {
            return null;
        }

        $weekStart = $this->currentWeekStart();
        $base      = $this->baseNumbers[$group];

        // Use a DB transaction + lock to prevent race conditions
        return DB::transaction(function () use ($group, $weekStart, $base) {
            $row = DB::table('inquiry_sequences')
                ->where('region_group', $group)
                ->where('week_start', $weekStart)
                ->lockForUpdate()
                ->first();

            if ($row) {
                $next = $row->last_number + 1;
                DB::table('inquiry_sequences')
                    ->where('id', $row->id)
                    ->update([
                        'last_number' => $next,
                        'updated_at'  => now(),
                    ]);
            } else {
                $next = $base + 1;
                DB::table('inquiry_sequences')->insert([
                    'region_group' => $group,
                    'week_start'   => $weekStart,
                    'last_number'  => $next,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            return $next;
        });
    }
}
