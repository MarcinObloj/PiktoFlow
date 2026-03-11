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

    protected $description = 'Pobiera obszerne słownictwo AAC z oficjalnego API ARASAAC';

    public function handle()
    {
        $vocabulary = [
            'Osoby' => ['Ja', 'Ty', 'Mama', 'Tata', 'Babcia', 'Dziadek', 'Brat', 'Siostra', 'Kolega', 'Koleżanka', 'Lekarz', 'Nauczyciel'],
            'Czasowniki' => ['Chcę', 'Lubię', 'Pić', 'Jeść', 'Spać', 'Grać', 'Idę', 'Stop', 'Daj', 'Boli', 'Pomóż', 'Patrz', 'Słuchaj', 'Myć', 'Ubrać', 'Czekaj', 'Szukać'],
            'Rzeczowniki' => ['Dom', 'Samochód', 'Pies', 'Kot', 'Zabawka', 'Książka', 'Telefon', 'Telewizor', 'Łóżko', 'Krzesło', 'Stół', 'Toaleta', 'Woda'],
            'Jedzenie' => ['Chleb', 'Masło', 'Mleko', 'Ser', 'Zupa', 'Jabłko', 'Banan', 'Sok', 'Ciastko', 'Czekolada', 'Mięso', 'Ziemniaki'],
            'Ubrania' => ['Koszulka', 'Spodnie', 'Buty', 'Skarpetki', 'Kurtka', 'Czapka', 'Majtki', 'Piżama'],
            'Ciało' => ['Głowa', 'Ręka', 'Noga', 'Oko', 'Ucho', 'Nos', 'Buzia', 'Brzuch', 'Zęby'],
            'Przymiotniki' => ['Dobre', 'Złe', 'Duży', 'Mały', 'Gorące', 'Zimne', 'Mokre', 'Suche', 'Czyste', 'Brudne', 'Szybki', 'Wolny'],
            'Emocje' => ['Wesoły', 'Smutny', 'Zły', 'Zmęczony', 'Zaskoczony', 'Znudzony', 'Boję się'],
            'Miejsca' => ['Szkoła', 'Przedszkole', 'Sklep', 'Park', 'Plac zabaw', 'Szpital', 'Basen'],
            'Zwroty' => ['Tak', 'Nie', 'Proszę', 'Dziękuję', 'Przepraszam', 'Cześć', 'Do widzenia', 'Nie wiem', 'Co to?']
        ];

        $colors = [
            'Osoby' => '#FFD1DC',
            'Czasowniki' => '#A1E9A1',
            'Rzeczowniki' => '#FFD700',
            'Jedzenie' => '#FFA07A',
            'Ubrania' => '#98FB98',
            'Ciało' => '#FFDEAD',
            'Przymiotniki' => '#87CEFA',
            'Emocje' => '#DDA0DD',
            'Miejsca' => '#F0E68C',
            'Zwroty' => '#D3D3D3'
        ];

        foreach ($vocabulary as $categoryName => $words) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['color' => $colors[$categoryName] ?? '#E5E7EB']
            );

            foreach ($words as $word) {
                if (Pictogram::where('name', $word)->exists()) {
                    $this->info("Pominięto (już istnieje): " . $word);
                    continue;
                }

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

                        Pictogram::create([
                            'name' => $word,
                            'category_id' => $category->id,
                            'image_path' => '/storage/' . $filename,
                            'is_custom' => false
                        ]);
                        $this->info("Sukces! Zapisano: " . $word);
                    }
                } else {
                    $this->warn("Nie znaleziono w bazie ARASAAC: " . $word);
                }
            }
        }

        $this->info("Pobieranie rozszerzonej bazy zakończone!");
    }
}
