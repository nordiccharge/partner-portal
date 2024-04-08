<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function import(Request $request) {
        if (!$request->run) {
            return 'Please add ?run=true to the URL to run the import';
        }
        if (!$request->name -|) {
            return 'Please add ?run=true to the URL to run the import';
        }
        $file = fopen(database_path('data/' . $request->name), 'r');
        echo '<p>Importing ' . $request->number . '</p>';
        $max = 1;
        if($request->number) {
            $max = (int)$request->number;
        }
        $count = 0;
        echo '<table>';
        echo '<tr><td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td>12</td><td>13</td><td>14</td><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td></tr>';
        while (($line = fgetcsv($file)) !== false && $count <= $max) {
            \Illuminate\Support\Facades\Log::debug('Running import on count ' . $count);
            echo '<tr>';
            foreach ($line as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            if (!$count == 0) {
                $type = $line[12];
                $installation_required = 0;
                $installation_id = null;
                $pipeline_id = 3;
                $stage_id = 3;

                // Installer Contacted
                if ($line['18'] == 1) {
                    $stage_id = 4;
                }

                // Installation Date Confirmed
                if ($line['18'] == 2) {
                    $stage_id = 5;
                }

                // Installation Completed
                if ($line['18'] == 3) {
                    $stage_id = 6;
                }

                // Online & Completed
                if ($line['18'] == 4) {
                    $stage_id = 7;
                }

                if ($line['18'] == 5) {
                    $stage_id = 8;
                }


                if ($type == 'stromlinet_11') {
                    $installation_required = 1;
                    $installation_id = 3;
                    \Illuminate\Support\Facades\Log::debug('Installation required on stromlinet_11');
                }

                if ($type == 'stromlinet_22') {
                    $installation_required = 1;
                    $installation_id = 4;
                    \Illuminate\Support\Facades\Log::debug('Installation required on stromlinet_22');
                }

                if ($type == 'stromlinet_return') {
                    $installation_required = 0;
                    // Return created
                    $stage_id = 1;
                    \Illuminate\Support\Facades\Log::debug('Installation not required on stromlinet_return');

                }

                $postal = \App\Models\Postal::where('postal', '=', $line[15])->first();
                $postal_id = $postal->id;
                $city_id = $postal->city->id;
                \Illuminate\Support\Facades\Log::debug('Postal: ' . $postal->postal . ' City: ' . $postal->city->name . ' City ID: ' . $postal->city->id . ' Postal ID: ' . $postal->id);

                // check pipeline_id
                // check installation_id
                // check stage_id
                // check inventory for order items
                // create comment for note
                // create comment for customer ID
                \Illuminate\Support\Facades\Log::debug('Creating order');
                $installation_date = null;
                if ($line[9] != '' && $line[9]) {
                    $installation_date = DateTime::createFromFormat('d-m-Y', $line[9]);
                }
                $order = \App\Models\Order::factory()->createOneQuietly([
                    'id' => (int)$line[13],
                    'shipping_address' => $line[1],
                    'team_id' => 5,
                    'pipeline_id' => $pipeline_id,
                    'stage_id' => $stage_id,
                    'installation_required' => $installation_required,
                    'installation_id' => $installation_id,
                    'installation_date' => $installation_date,
                    'note' => 'Imported from Nordic Charge Cloud ––– Customer: ' . $line[6],
                    'country_id' => 1,
                    'customer_email' => $line[7],
                    'customer_first_name' => $line[8],
                    'customer_last_name' => $line[10],
                    'customer_phone' => $line[14],
                    'order_reference' => $line[0],
                    'postal_id' => $postal_id,
                    'city_id' => $city_id,
                    'created_at' => $line[20]
                ]);

                \Illuminate\Support\Facades\Log::debug('Order created ' . $order->id);
                if ($line[2] == 'ZM000688') {
                    \App\Models\OrderItem::factory()->createOneQuietly([
                        'order_id' => $order->id,
                        'inventory_id' => 9,
                        'quantity' => 1,
                        'price' => 0,
                    ]);
                }

                if ($line[2] == 'EH001-black' || $line[2] == 'EC001-black') {
                    \App\Models\OrderItem::factory()->createOneQuietly([
                        'order_id' => $order->id,
                        'inventory_id' => 20,
                        'quantity' => 1,
                        'price' => 0,
                    ]);
                }

            }
            echo '</tr>';
            $count++;
        }
        echo '</table>';

        return '<style>
                tr {
                  border-bottom: 1px solid #ddd;
                }
                tr:nth-child(even) {
                    background-color: #D6EEEE;
                }
            </style>';
    }
}
