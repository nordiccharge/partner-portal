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
        Schema::create('postals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id');
            $table->foreignId('city_id');
            $table->unsignedBigInteger('postal')
                ->unique();
            $table->foreignId('installer_id')
                ->nullable();
            $table->boolean('active')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postals');
    }
};
