<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   public function run(): void
   {

       $foodCategory = \App\Models\Category::create([
           'name' => 'Jedzenie',
           'color' => '#FFD700'
       ]);


       $emotionsCategory = \App\Models\Category::create([
           'name' => 'Emocje',
           'color' => '#87CEFA'
       ]);

       \App\Models\Pictogram::create([
           'name' => 'Jabłko',
           'image_path' => '/pictograms/apple.png', // Na razie dajemy fałszywą ścieżkę
           'category_id' => $foodCategory->id,
           'is_custom' => false
       ]);

       \App\Models\Pictogram::create([
           'name' => 'Woda',
           'image_path' => '/pictograms/water.png',
           'category_id' => $foodCategory->id,
           'is_custom' => false
       ]);


       \App\Models\Pictogram::create([
           'name' => 'Szczęśliwy',
           'image_path' => '/pictograms/happy.png',
           'category_id' => $emotionsCategory->id,
           'is_custom' => false
       ]);
   }
}
