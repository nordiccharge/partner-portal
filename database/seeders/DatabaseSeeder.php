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
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Pipe;

class DatabaseSeeder extends Seeder
{

    private function seedCitiesDenmark() {


        $file = fopen(base_path('database/data/cities_dk.csv'), 'r');

        while (($line = fgetcsv($file)) !== false) {
            City::create([
                'name' => $line[0],
                'country_id' => 1,
            ]);
        }
    }

    private function seedPostalsDenmark() {
        $file = fopen(base_path('database/data/postals_dk.csv'), 'r');

        while (($line = fgetcsv($file)) !== false) {
            try {
                $city = City::where('name', $line[1]);
                if (City::where('name', $line[1])->exists()) {
                    Postal::create([
                        'postal' => $line[0],
                        'country_id' => 1,
                        'city_id' => $city->first()->id,
                    ]);
                }
            } catch (\Exception $e) {
                continue;
            }

        }
    }

    private function seedInstallers() {
            $file = fopen(base_path('database/data/installers_dk.csv'), 'r');

            while (($line = fgetcsv($file)) !== false) {
                $company = Company::create([
                    'company_type_id' => 1,
                    'name' => $line[0],
                    'vat_number' => $line[1],
                    'sender_name' => $line[0],
                    'sender_address' => $line[2],
                    'sender_zip' => $line[3],
                    'sender_city' => $line[4],
                    'sender_country' => $line[5],
                    'contact_email' => $line[6],
                ]);

                Installer::create([
                    'company_id' => $company->id,
                    'contact_email' => $line[6],
                    'contact_phone' => $line[7],
                ]);
            }
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Sebastian Bak Lundahl',
            'email' => 'sebastian@nordiccharge.com',
            'password' => bcrypt('password'),
            'is_admin' => 1
        ]);

        Pipeline::create([
            'name' => 'Private Costumer',
            'shipping_type' => 'gls_private_delivery',
        ]);

        Stage::create([
            'pipeline_id' => 1,
            'name' => 'Order Created',
            'order' => 1,
            'state' => 'action',
        ]);

        Stage::create([
            'pipeline_id' => 1,
            'name' => 'Installer Contacted',
            'order' => 2,
            'state' => 'step',
        ]);

        Stage::create([
            'pipeline_id' => 1,
            'name' => 'Installation Date Confirmed',
            'order' => 3,
            'state' => 'action',
        ]);

        Stage::create([
            'pipeline_id' => 1,
            'name' => 'Installation Completed',
            'order' => 3,
            'state' => 'action',
        ]);

        Stage::create([
            'pipeline_id' => 1,
            'name' => 'Online & Completed',
            'order' => 4,
            'state' => 'completed',
        ]);

        Stage::create([
            'pipeline_id' => 1,
            'name' => 'Aborted',
            'order' => 5,
            'state' => 'aborted',
        ]);

        CompanyType::create([
            'name' => 'Installer'
        ]);

        CompanyType::create([
            'name' => 'Partner'
        ]);

        Company::create([
            'company_type_id' => 2,
            'name' => 'Nordic Charge',
            'sender_name' => 'Nordic Charge ApS',
            'sender_address' => 'Kantatevej 30',
            'sender_zip' => '2730',
            'sender_city' => 'Herlev',
            'sender_country' => 'DK',
            'sender_phone' => '+45 22 22 93 70',
            'sender_email' => 'dk@nordiccharge.com'
        ]);

        Team::create([
            'name' => 'Demo Team',
            'company_id' => 1,
            'user_id' => 1,
            'secret_key' => Str::random(50),
        ])->users()->attach(User::find(1));

        Country::create([
            'short_name' => 'DK',
            'name' => 'Denmark'
        ]);

        $this->seedCitiesDenmark();
        $this->seedPostalsDenmark();
        $this->seedInstallers();

        Brand::create([
            'name' => 'Zaptec',
        ]);

        Product::create([
            'name' => 'Zaptec Go',
            'sku' => 'ZM000688',
            'brand_id' => 1,
        ]);

    }

}
