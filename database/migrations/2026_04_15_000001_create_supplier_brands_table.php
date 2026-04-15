<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_brands', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->string('brand_name');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->index(['supplier_name', 'brand_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_brands');
    }
};
