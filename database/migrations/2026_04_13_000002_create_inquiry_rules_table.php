<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inquiry_rules')) {
            return;
        }

        Schema::create('inquiry_rules', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 100);       // e.g. "Africa", "GCC"
            $table->integer('base_number');           // e.g. 10000, 20000
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inquiry_rule_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_rule_id')->constrained('inquiry_rules')->onDelete('cascade');
            $table->string('country_name', 150);     // matched case-insensitively
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_rule_countries');
        Schema::dropIfExists('inquiry_rules');
    }
};
