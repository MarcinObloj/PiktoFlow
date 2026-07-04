<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$words = ['chcę', 'tak', 'nie', 'pomocy', 'stop', 'koniec', 'jeszcze', 'to', 'teraz', 'później', 'gotowe', 'ja', 'ty', 'mama', 'tata', 'dziecko', 'pani', 'pan', 'babcia', 'dziadek', 'kolega', 'lekarz', 'iść', 'spać', 'jeść', 'pić', 'bawić się', 'oglądać', 'słuchać', 'czytać', 'myć', 'ubierać', 'siadać', 'stać', 'rysować', 'skakać', 'lubię', 'nie lubię', 'dobrze', 'źle', 'wesoły', 'smutny', 'zły', 'zmęczony', 'boli', 'kocham', 'strach', 'dom', 'szkoła', 'plac zabaw', 'sklep', 'łóżko', 'zabawka', 'książka', 'telefon', 'toaleta', 'jedzenie', 'picie', 'auto', 'rower', 'jabłko', 'banan', 'woda', 'sok', 'zupa', 'ciastko'];
$overrides = [
    'ja' => 'mnie', 'ty' => 'ciebie', 'mama' => 'matka', 'tata' => 'ojciec', 'szczęśliwy' => 'uśmiech', 
    'smutny' => 'smutek', 'zły' => 'gniew', 'boję się' => 'przestraszony', 'nie lubię' => 'nie znosić', 
    'źle' => 'niepoprawnie', 'jabłko' => 'owoc jabłoni', 'woda' => 'woda mineralna', 'banan' => 'owoc banana', 
    'picie' => 'napoje', 'chcę' => 'chcieć', 'do widzenia' => 'pożegnanie', 'nie wiem' => 'nie rozumieć'
];

foreach($words as $w) {
    $term = $overrides[$w] ?? $w;
    $res = Http::get('https://api.arasaac.org/api/pictograms/pl/search/'.urlencode($term));
    if($res->successful() && count($res->json())>0) {
        $id = $res->json()[0]['_id'];
        $data = Http::get('https://api.arasaac.org/api/pictograms/pl/'.$id)->json();
        $k = $data['keywords'][0]['keyword'] ?? 'null';
        echo "$w ($term) => ID: $id ($k)\n";
    } else {
        echo "$w ($term) => NOT FOUND\n";
    }
    usleep(100000);
}
