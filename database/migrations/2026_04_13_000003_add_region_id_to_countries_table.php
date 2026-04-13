<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->uuid('region_id')->nullable()->after('name');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });
    }
};
