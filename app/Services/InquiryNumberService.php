<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InquiryNumberService
{
    /**
     * Get the Monday date of the current week (as a date string YYYY-MM-DD).
     */
    public function currentWeekStart(): string
    {
        return Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    /**
     * Load all active rules from the DB.
     * Returns a collection of rules, each with a ->countries array (lowercase).
     */
    protected function loadRules(): \Illuminate\Support\Collection
    {
        $rules = DB::table('inquiry_rules')
            ->where('is_active', true)
            ->get();

        if ($rules->isEmpty()) {
            return collect();
        }

        $ruleIds = $rules->pluck('id')->toArray();
        $countryRows = DB::table('inquiry_rule_countries')
            ->whereIn('inquiry_rule_id', $ruleIds)
            ->get()
            ->groupBy('inquiry_rule_id');

        return $rules->map(function ($rule) use ($countryRows) {
            $rule->countries = ($countryRows[$rule->id] ?? collect())
                ->pluck('country_name')
                ->map(fn($c) => strtolower(trim($c)))
                ->toArray();
            return $rule;
        });
    }

    /**
     * Determine the matching rule for a given company ID.
     * Returns the rule object or null if no match.
     */
    public function getRuleForCompany(string|int $companyId): ?object
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
        $rules = $this->loadRules();

        foreach ($rules as $rule) {
            if (in_array($countryLower, $rule->countries, true)) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Peek at the next inquiry number for a company (without incrementing).
     * Returns null if no rule matches.
     */
    public function peekNextNumber(string|int $companyId): ?int
    {
        $rule = $this->getRuleForCompany($companyId);
        if (!$rule) {
            return null;
        }

        $weekStart = $this->currentWeekStart();
        $base      = $rule->base_number;

        $row = DB::table('inquiry_sequences')
            ->where('region_group', $rule->group_name)
            ->where('week_start', $weekStart)
            ->first();

        return $row ? ($row->last_number + 1) : ($base + 1);
    }

    /**
     * Atomically assign and return the next inquiry number for a company.
     * Increments the counter in the DB. Returns null if no rule matches.
     */
    public function assignNextNumber(string|int $companyId): ?int
    {
        $rule = $this->getRuleForCompany($companyId);
        if (!$rule) {
            return null;
        }

        $weekStart = $this->currentWeekStart();
        $base      = $rule->base_number;
        $group     = $rule->group_name;

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
