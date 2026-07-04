<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\Http;

$words = ['stop' => 'zatrzymać się', 'nie lubię' => 'nie lubić', 'boli' => 'ból', 'kocham' => 'miłość', 'jabłko' => 'jabłko', 'banan' => 'banan', 'woda' => 'woda', 'sok' => 'sok z owoców', 'zupa' => 'zupa', 'picie' => 'picie', 'toaleta' => 'sedes', 'bawić się' => 'bawić się zabawka'];
foreach($words as $w => $term) {
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
