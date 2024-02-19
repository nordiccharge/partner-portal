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
        Schema::create('return_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')
                ->constrained();
            $table->foreignId('order_id')
                ->unique()
                ->constrained();
            $table->foreignId('pipeline_id')
                ->nullable()
                ->constrained();
            $table->text('reason');
            $table->string('state');
            $table->boolean('shipping_label')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_orders');
    }
};
