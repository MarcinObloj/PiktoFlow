<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Pictogram;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $food = Category::create(['name' => 'Jedzenie', 'color' => '#FFD700']);
        $emotions = Category::create(['name' => 'Emocje', 'color' => '#87CEFA']);
        $people = Category::create(['name' => 'Osoby', 'color' => '#FF69B4']);

        $baseIcons = [
            ['name' => 'Jabłko', 'id' => '2558', 'cat' => $food->id],
            ['name' => 'Woda', 'id' => '2328', 'cat' => $food->id],
            ['name' => 'Banan', 'id' => '2492', 'cat' => $food->id],

            ['name' => 'Szczęśliwy', 'id' => '3216', 'cat' => $emotions->id],
            ['name' => 'Smutny', 'id' => '3222', 'cat' => $emotions->id],
            ['name' => 'Zły', 'id' => '3219', 'cat' => $emotions->id],

            ['name' => 'Mama', 'id' => '2511', 'cat' => $people->id],
            ['name' => 'Tata', 'id' => '2512', 'cat' => $people->id],
            ['name' => 'Ja', 'id' => '2501', 'cat' => $people->id],
            ['name' => 'Ty', 'id' => '2503', 'cat' => $people->id],
        ];

        foreach ($baseIcons as $icon) {
            Pictogram::create([
                'name' => $icon['name'],
                'image_path' => "https://static.arasaac.org/pictograms/{$icon['id']}/{$icon['id']}_300.png",
                'category_id' => $icon['cat'],
                'is_custom' => false
            ]);
        }
    }
}
