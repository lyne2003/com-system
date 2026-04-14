<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\MouserService;
use App\Services\DigiKeyService;
use App\Services\TiService;

class SourcingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120; // 2 minutes per item

    protected string $rfqId;
    protected object $item;
    protected int $totalItems;

    public function __construct(string $rfqId, object $item, int $totalItems)
    {
        $this->rfqId      = $rfqId;
        $this->item       = $item;
        $this->totalItems = $totalItems;
    }

    public function handle(): void
    {
        $partNumber = trim($this->item->partnumber ?? '');
        $qty        = (int) ($this->item->qty ?? 1);

        if (empty($partNumber)) {
            $this->markDoneIfLast();
            return;
        }

        $mouser  = new MouserService();
        $digikey = new DigiKeyService();
        $ti      = new TiService();

        $suppliers = [
            $mouser->search($partNumber, $qty),
            $digikey->search($partNumber, $qty),
            $ti->search($partNumber, $qty),
        ];

        $now = now();
        $rows = [];

        foreach ($suppliers as $result) {
            $rows[] = [
                'id'              => Str::uuid(),
                'rfq_id'          => $this->rfqId,
                'item_id'         => $this->item->id,
                'partnumber'      => $partNumber,
                'supplier'        => $result['supplier'] ?? 'unknown',
                'status'          => $result['status'] ?? 'error',
                'description'     => $result['description'] ?? null,
                'manufacturer'    => $result['manufacturer'] ?? null,
                'manufacturer_pn' => $result['manufacturer_pn'] ?? null,
                'unit_price'      => $result['unit_price'] ?? null,
                'availability'    => $result['availability'] ?? null,
                'stock_status'    => $result['stock_status'] ?? null,
                'lead_time'       => $result['lead_time'] ?? null,
                'moq'             => $result['moq'] ?? null,
                'package_type'    => $result['package_type'] ?? null,
                'package_qty'     => $result['package_qty'] ?? null,
                'datasheet_url'   => $result['datasheet_url'] ?? null,
                'category'        => $result['category'] ?? null,
                'raw_response'    => $result['raw_response'] ?? null,
                'sourced_at'      => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('sourcing_results')->insert($rows);
        }

        $this->markDoneIfLast();
    }

    /**
     * Check if all items have been sourced; if so, mark RFQ as completed.
     */
    protected function markDoneIfLast(): void
    {
        $sourced = DB::table('sourcing_results')
            ->where('rfq_id', $this->rfqId)
            ->distinct('item_id')
            ->count('item_id');

        if ($sourced >= $this->totalItems) {
            DB::table('rfqs')
                ->where('id', $this->rfqId)
                ->update(['sourcing_status' => 'completed', 'updated_at' => now()]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        // If a job fails permanently, mark the RFQ as failed
        DB::table('rfqs')
            ->where('id', $this->rfqId)
            ->update(['sourcing_status' => 'failed', 'updated_at' => now()]);
    }
}
