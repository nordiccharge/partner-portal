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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Order Details
            $table->foreignId('team_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('pipeline_id')
                ->nullable()
                ->constrained();
            $table->foreignId('stage_id')
                ->nullable()
                ->constrained();
            $table->string('order_reference')
                ->nullable();
            $table->longText('note')
                ->nullable();

            // Customer Details
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            // Shipping Details
            $table->string('shipping_address');
            $table->foreignId('postal_id');
            $table->string('city_id');
            $table->foreignId('country_id');
            $table->string('tracking_code')
                ->nullable();

            // Installation Details
            $table->boolean('installation_required')
                ->default(false);
            $table->foreignId('installation_id')
                ->nullable()
                ->constrained();
            $table->decimal('installation_price')
                ->nullable();
            $table->date('wished_installation_date')
                ->nullable();
            $table->date('installation_date')
                ->nullable();
            $table->foreignId('installer_id')
                    ->nullable()
                    ->constrained();

            $table->text('error_message')
                ->nullable();

            // Other
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
