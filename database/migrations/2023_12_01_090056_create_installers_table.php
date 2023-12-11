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
        Schema::create('installers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained();
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('invoice_email')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installers');
    }
};
