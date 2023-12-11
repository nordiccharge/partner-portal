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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Company Details
            $table->foreignId('company_type_id')
                ->nullable()
                ->constrained();
            $table->string('name');
            $table->text('description')
                ->nullable();
            $table->string('contact_email')
                ->nullable();
            $table->string('contact_phone')
                ->nullable();
            $table->string('invoice_email')
                ->nullable();

            // Shipping Details
            $table->string('sender_name');
            $table->string('sender_attention')
                ->nullable();
            $table->string('sender_address');
            $table->string('sender_address2')
                ->nullable();
            $table->string('sender_zip');
            $table->string('sender_city');
            $table->string('sender_country');
            $table->string('sender_state')
                ->nullable();
            $table->string('sender_phone')
                ->nullable();
            $table->string('sender_email')
                ->nullable();

            // Other
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
