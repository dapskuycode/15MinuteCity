<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('walkability_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_id')->constrained('user_searches')->onDelete('cascade');
            $table->geometry('zone_polygon'); // Tipe data geospasial
            $table->string('zone_type', 50);
            $table->integer('travel_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('walkability_zones');
    }
};
