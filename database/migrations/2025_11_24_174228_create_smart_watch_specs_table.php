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
        Schema::create('smart_watch_specs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Smart Watch Specifications
            $table->string('display_type')->nullable();        // AMOLED / Retina / LCD
            $table->string('display_size')->nullable();        // 1.2", 1.4", 2.0"
            $table->string('resolution')->nullable();          // 410×502, 484×396
            $table->string('chipset')->nullable();             // S9, Exynos W930, etc.
            $table->string('ram')->nullable();                 // 1GB, 2GB
            $table->string('storage')->nullable();             // 16GB, 32GB, 64GB
            $table->string('battery_life')->nullable();        // 18 hours, 2 days, 14 days
            $table->string('charging_type')->nullable();       // Magnetic, Wireless
            $table->string('gps')->nullable();                 // Yes / No
            $table->string('water_resistance')->nullable();    // 5ATM, IP68, WR50
            $table->string('sensors')->nullable();             // HR, SpO2, ECG, Compass
            $table->string('connectivity')->nullable();        // Bluetooth, LTE, WiFi
            $table->string('operating_system')->nullable();    // watchOS, Wear OS, HarmonyOS
            $table->string('compatibility')->nullable();       // iOS / Android
            $table->string('weight')->nullable();              // 32g, 45g
            $table->string('warranty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_watch_specs');
    }
};
