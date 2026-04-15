<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supplier_subcategories')) {
            return;
        }

        Schema::create('supplier_subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name', 255);
            $table->string('subcategory_name', 255);
            $table->integer('count')->default(0);
            $table->timestamps();

            $table->index(['supplier_name', 'subcategory_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_subcategories');
    }
};
