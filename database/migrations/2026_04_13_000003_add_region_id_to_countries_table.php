<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create country_region junction table if it doesn't exist
        if (!Schema::hasTable('country_region')) {
            Schema::create('country_region', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('country_id');
                $table->uuid('region_id');
                $table->timestamp('created_at')->nullable()->default(now());
                $table->unique(['country_id', 'region_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('country_region');
    }
};
