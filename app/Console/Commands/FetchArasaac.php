<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Pictogram;

class FetchArasaac extends Command
{
    protected $signature = 'arasaac:fetch';

    protected $description = 'Pobiera podstawowe słownictwo AAC z oficjalnego API ARASAAC';

    public function handle()
    {
        $vocabulary = [
            'Osoby' => ['Ja', 'Ty', 'Mama', 'Tata', 'Pan', 'Pani', 'Dziecko'],
            'Czasowniki' => ['Chcę', 'Lubię', 'Pić', 'Jeść', 'Spać', 'Grać', 'Idę', 'Stop'],
            'Rzeczowniki' => ['Dom', 'Samochód', 'Pies', 'Kot', 'Zabawka', 'Książka'],
            'Przymiotniki' => ['Dobre', 'Złe', 'Duży', 'Mały', 'Gorące', 'Zimne'],
            'Zwroty' => ['Tak', 'Nie', 'Proszę', 'Dziękuję', 'Przepraszam', 'Cześć']
        ];

        $colors = [
            'Osoby' => '#FFD1DC',
            'Czasowniki' => '#A1E9A1',
            'Rzeczowniki' => '#FFD700',
            'Przymiotniki' => '#87CEFA',
            'Zwroty' => '#DDA0DD'
        ];

        foreach ($vocabulary as $categoryName => $words) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['color' => $colors[$categoryName] ?? '#E5E7EB']
            );

            foreach ($words as $word) {
                $this->info("Szukam w ARASAAC: " . $word);

                $response = Http::get("https://api.arasaac.org/api/pictograms/pl/search/" . urlencode($word));

                if ($response->successful() && !empty($response->json())) {
                    $data = $response->json()[0];
                    $arasaacId = $data['_id'];

                    $imageUrl = "https://static.arasaac.org/pictograms/{$arasaacId}/{$arasaacId}_300.png";
                    $imageResponse = Http::get($imageUrl);

                    if ($imageResponse->successful()) {
                        $filename = "pictograms/arasaac_{$arasaacId}.png";
                        Storage::disk('public')->put($filename, $imageResponse->body());

                        Pictogram::firstOrCreate(
                            ['name' => $word, 'category_id' => $category->id],
                            [
                                'image_path' => '/storage/' . $filename,
                                'is_custom' => false
                            ]
                        );
                        $this->info("Sukces! Zapisano: " . $word);
                    }
                } else {
                    $this->warn("Nie znaleziono w bazie ARASAAC: " . $word);
                }
            }
        }

        $this->info("Pobieranie bazy startowej zakończone!");
    }
}
