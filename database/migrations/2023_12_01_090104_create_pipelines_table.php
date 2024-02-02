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
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')
                ->nullable();
            $table->string('name');
            $table->boolean('shipping')
                ->default(false);
            $table->string('shipping_type')
                ->nullable();
            $table->string('automation_type')
                ->nullable();
            $table->decimal('shipping_price')
                ->default(0);
            $table->decimal('nc_price')
                ->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipelines');
    }
};
