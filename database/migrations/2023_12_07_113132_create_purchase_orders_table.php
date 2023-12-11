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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('team_id');

            // Order Details
            $table->longText('note')
                ->nullable();
            $table->string('status')
                ->default('Order Created');

            // Shipping Details
            $table->boolean('use_dropshipping')
                ->default(true);
            $table->string('shipping_address')
                ->nullable();
            $table->integer('postal')
                ->nullable();
            $table->string('city')
                ->nullable();
            $table->string('country')
                ->nullable();
            $table->string('tracking_code')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
