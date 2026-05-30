<?php

namespace Database\Seeders;

use App\Models\TemplateSet;
use App\Models\TemplateSetPictogram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TemplateSetSeeder extends Seeder
{
    private function fetchArasaac(string $word): ?string
    {
        $overrides = [
            'mama' => 'matka', 'tata' => 'ojciec', 'myć zęby' => 'mycie zębów',
            'śniadanie' => 'jeść', 'obiad' => 'posiłek', 'kolacja' => 'jeść wieczorem',
            'wesoły' => 'uśmiech', 'smutny' => 'smutek', 'zły' => 'gniew',
            'pić' => 'napoje', 'jabłko' => 'owoc jabłoni', 'chleb' => 'pieczywo',
            'rysunek' => 'rysować', 'puzzle' => 'układanka',
        ];
        $term = $overrides[mb_strtolower($word)] ?? $word;
        $resp = Http::get('https://api.arasaac.org/api/pictograms/pl/search/' . urlencode($term));
        if ($resp->successful() && count($resp->json()) > 0) {
            $id = $resp->json()[0]['_id'];
            return "https://static.arasaac.org/pictograms/{$id}/{$id}_300.png";
        }
        return null;
    }

    public function run(): void
    {
        $sets = [
            [
                'name' => 'Poranna rutyna',
                'description' => 'Piktogramy pomagające w organizacji porannych czynności',
                'icon' => '🌅',
                'color' => '#f59e0b',
                'words' => [
                    ['name' => 'Wstawanie', 'cat' => 'Czynności'],
                    ['name' => 'Toaleta', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Myć zęby', 'cat' => 'Czynności'],
                    ['name' => 'Ubierać', 'cat' => 'Czynności'],
                    ['name' => 'Śniadanie', 'cat' => 'Jedzenie'],
                    ['name' => 'Szkoła', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Autobus', 'cat' => 'Miejsca i Rzeczy'],
                ],
            ],
            [
                'name' => 'Emocje',
                'description' => 'Wyrażanie uczuć i stanów emocjonalnych',
                'icon' => '😊',
                'color' => '#ef4444',
                'words' => [
                    ['name' => 'Wesoły', 'cat' => 'Emocje'],
                    ['name' => 'Smutny', 'cat' => 'Emocje'],
                    ['name' => 'Zły', 'cat' => 'Emocje'],
                    ['name' => 'Zmęczony', 'cat' => 'Emocje'],
                    ['name' => 'Strach', 'cat' => 'Emocje'],
                    ['name' => 'Kocham', 'cat' => 'Emocje'],
                    ['name' => 'Boli', 'cat' => 'Emocje'],
                    ['name' => 'Lubię', 'cat' => 'Emocje'],
                ],
            ],
            [
                'name' => 'Jedzenie i picie',
                'description' => 'Popularne produkty spożywcze i napoje',
                'icon' => '🍎',
                'color' => '#10b981',
                'words' => [
                    ['name' => 'Jabłko', 'cat' => 'Jedzenie'],
                    ['name' => 'Banan', 'cat' => 'Jedzenie'],
                    ['name' => 'Chleb', 'cat' => 'Jedzenie'],
                    ['name' => 'Mleko', 'cat' => 'Jedzenie'],
                    ['name' => 'Woda', 'cat' => 'Jedzenie'],
                    ['name' => 'Sok', 'cat' => 'Jedzenie'],
                    ['name' => 'Zupa', 'cat' => 'Jedzenie'],
                    ['name' => 'Ciastko', 'cat' => 'Jedzenie'],
                ],
            ],
            [
                'name' => 'Szkoła',
                'description' => 'Piktogramy związane z zajęciami szkolnymi',
                'icon' => '🏫',
                'color' => '#3b82f6',
                'words' => [
                    ['name' => 'Szkoła', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Czytać', 'cat' => 'Czynności'],
                    ['name' => 'Pisać', 'cat' => 'Czynności'],
                    ['name' => 'Rysować', 'cat' => 'Czynności'],
                    ['name' => 'Książka', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Pani', 'cat' => 'Osoby'],
                    ['name' => 'Kolega', 'cat' => 'Osoby'],
                    ['name' => 'Przerwa', 'cat' => 'Czynności'],
                ],
            ],
            [
                'name' => 'Zabawa',
                'description' => 'Aktywności rekreacyjne i zabawki',
                'icon' => '🎮',
                'color' => '#8b5cf6',
                'words' => [
                    ['name' => 'Bawić się', 'cat' => 'Czynności'],
                    ['name' => 'Piłka', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Zabawka', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Plac zabaw', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Rysunek', 'cat' => 'Czynności'],
                    ['name' => 'Puzzle', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Muzyka', 'cat' => 'Miejsca i Rzeczy'],
                    ['name' => 'Skakać', 'cat' => 'Czynności'],
                ],
            ],
        ];

        foreach ($sets as $setData) {
            if (TemplateSet::where('name', $setData['name'])->exists()) {
                continue;
            }

            $set = TemplateSet::create([
                'name' => $setData['name'],
                'description' => $setData['description'],
                'icon' => $setData['icon'],
                'color' => $setData['color'],
            ]);

            foreach ($setData['words'] as $pos => $item) {
                $imageUrl = $this->fetchArasaac($item['name']);
                if ($imageUrl) {
                    TemplateSetPictogram::create([
                        'template_set_id' => $set->id,
                        'name' => $item['name'],
                        'image_path' => $imageUrl,
                        'category_name' => $item['cat'],
                        'position' => $pos,
                    ]);
                }
            }
        }
    }
}
