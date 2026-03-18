<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sourcing_results')) {
            return;
        }

        Schema::create('sourcing_results', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(\Illuminate\Support\Facades\DB::raw('gen_random_uuid()'));
            $table->uuid('rfq_id');
            $table->uuid('item_id');
            $table->string('partnumber')->nullable();
            $table->string('supplier', 50); // 'mouser' | 'digikey' | 'ti'

            // Status
            $table->string('status', 50)->nullable(); // 'found' | 'not_found' | 'no_stock' | 'error'

            // Result fields
            $table->text('description')->nullable();
            $table->string('manufacturer', 255)->nullable();
            $table->string('manufacturer_pn', 255)->nullable();
            $table->decimal('unit_price', 12, 4)->nullable();
            $table->integer('availability')->nullable();
            $table->string('stock_status', 100)->nullable();
            $table->string('lead_time', 100)->nullable();
            $table->integer('moq')->nullable();
            $table->string('package_type', 100)->nullable();
            $table->integer('package_qty')->nullable();
            $table->text('datasheet_url')->nullable();
            $table->string('category', 255)->nullable();
            $table->text('raw_response')->nullable();

            $table->timestamp('sourced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sourcing_results');
    }
};
