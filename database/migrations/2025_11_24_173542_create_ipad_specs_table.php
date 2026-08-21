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
        Schema::create('ipad_specs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('chipset')->nullable();         // A14, A15, M1, M2, etc.
            $table->string('ram')->nullable();             // 4GB, 6GB, 8GB
            $table->string('storage')->nullable();         // 64GB, 256GB, 512GB
            $table->string('display_size')->nullable();    // 10.2", 11", 12.9"
            $table->string('battery')->nullable();         // 28.6 Wh, 10 hours usage
            $table->string('camera_rear')->nullable();     // 8MP, 12MP
            $table->string('operating_system')->nullable(); // iPadOS 18
            $table->string('network')->nullable();         // WiFi, WiFi+Cellular, 5G
            $table->string('accessories')->nullable();     // Apple Pencil Gen 1/2
            $table->string('weight')->nullable();
            $table->string('warranty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipad_specs');
    }
};
