<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Company;
use App\Models\CompanyType;
use App\Models\Country;
use App\Models\Installer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pipeline;
use App\Models\Postal;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Sebastian Bak Lundahl',
            'email' => 'sebastian@nordiccharge.com',
            'password' => bcrypt('123sebHnj88mts??'),
            'is_admin' => 1
        ]);

        Pipeline::factory()->create();

        CompanyType::factory()->create();

        Country::factory()->create();

    }
}
