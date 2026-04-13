<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inquiry_sequences')) {
            return;
        }

        Schema::create('inquiry_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('region_group', 50); // 'africa' | 'gcc'
            $table->date('week_start');          // Monday of the current week
            $table->integer('last_number');      // last assigned number this week
            $table->timestamps();

            $table->unique(['region_group', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_sequences');
    }
};
