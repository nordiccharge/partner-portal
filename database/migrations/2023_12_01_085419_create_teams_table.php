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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained();
            $table->boolean('active')
                ->default(true);
            $table->string('name');
            $table->text('description')
                ->nullable();

            // API Configuration
            $table->string('secret_key')
                ->unique();
            $table->boolean('basic_api')
                ->default(false);
            $table->boolean('endpoint')
                ->default(false);
            $table->string('endpoint_url')
                ->nullable();
            $table->boolean('shipping_api_send')
                ->default(false);
            $table->boolean('shipping_api_get')
                ->default(false);
            $table->boolean('woocommerce_api')
                ->default(false);
            $table->boolean('shopify_api')
                ->default(false);
            $table->boolean('backend_api')
                ->default(false);
            $table->string('backend_api_service')
                ->nullable();
            $table->string('cubs_api')
                ->default(false);

            // SendGrid Configuration
            $table->boolean('allow_sendgrid')
                ->default(false);
            $table->string('sendgrid_name')
                ->nullable();
            $table->string('sendgrid_email')
                ->nullable();
            $table->string('sendgrid_url')
                ->nullable();
            $table->boolean('sendgrid_auto_installer_allow')
                ->default(false);

                // SendGrid Order Created Email
                $table->boolean('sendgrid_order_created_allow')
                    ->default(false);
                $table->string('sendgrid_order_created_id')
                    ->nullable();

                // SendGrid Package Shipped Email
                $table->boolean('sendgrid_order_shipped_allow')
                    ->default(false);
                $table->string('sendgrid_order_shipped_id')
                    ->nullable();

            // Other
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->foreignId('team_id');
            $table->foreignId('user_id');
            $table->string('role')
                ->default('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
        Schema::dropIfExists('team_user');
    }
};
