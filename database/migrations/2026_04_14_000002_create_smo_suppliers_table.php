<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smo_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('active_count')->default(0);
            $table->integer('passive_count')->default(0);
            $table->timestamps();
        });

        // Seed with the data from the Excel sheet
        $suppliers = [
            ['name' => 'Ariat',      'active_count' => 0,    'passive_count' => 208],
            ['name' => 'Asai Kosan', 'active_count' => 494,  'passive_count' => 191],
            ['name' => 'ATP',        'active_count' => 40,   'passive_count' => 2998],
            ['name' => 'Brightmile', 'active_count' => 16,   'passive_count' => 4],
            ['name' => 'CapXon',     'active_count' => 0,    'passive_count' => 0],
            ['name' => 'Crassus',    'active_count' => 0,    'passive_count' => 1],
            ['name' => 'Flyking',    'active_count' => 0,    'passive_count' => 2],
            ['name' => 'Holtek',     'active_count' => 0,    'passive_count' => 0],
            ['name' => 'Linkic',     'active_count' => 0,    'passive_count' => 0],
            ['name' => 'Kehuite',    'active_count' => 916,  'passive_count' => 162],
            ['name' => 'Kingbright', 'active_count' => 3,    'passive_count' => 2],
            ['name' => 'Liangxin',   'active_count' => 0,    'passive_count' => 4],
            ['name' => 'Macronix',   'active_count' => 3,    'passive_count' => 0],
            ['name' => 'Matec',      'active_count' => 0,    'passive_count' => 13],
            ['name' => 'Perceptive', 'active_count' => 1568, 'passive_count' => 847],
            ['name' => 'Pinrex',     'active_count' => 0,    'passive_count' => 5],
            ['name' => 'Samwha',     'active_count' => 0,    'passive_count' => 0],
            ['name' => 'Shainor',    'active_count' => 2,    'passive_count' => 293],
            ['name' => 'SMYG',       'active_count' => 82,   'passive_count' => 2759],
            ['name' => 'Thorlabs',   'active_count' => 0,    'passive_count' => 0],
            ['name' => 'USIE',       'active_count' => 0,    'passive_count' => 92],
            ['name' => 'Wynn',       'active_count' => 0,    'passive_count' => 103],
        ];

        $now = now();
        foreach ($suppliers as &$s) {
            $s['created_at'] = $now;
            $s['updated_at'] = $now;
        }

        DB::table('smo_suppliers')->insert($suppliers);
    }

    public function down(): void
    {
        Schema::dropIfExists('smo_suppliers');
    }
};
