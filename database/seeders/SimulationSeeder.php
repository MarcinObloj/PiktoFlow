<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pictogram;
use Carbon\Carbon;

class SimulationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // Pobieramy 5 losowych piktogramów z bazy, żeby mieć w co "klikać"
        $pictogramIds = Pictogram::inRandomOrder()->limit(5)->pluck('id')->toArray();
        if (empty($pictogramIds)) {
            $this->command->error('Baza piktogramów jest pusta! Zaloguj się i dodaj chociaż kilka słów, żeby symulacja zadziałała.');
            return;
        }

        foreach ($users as $user) {
            // Tworzymy 3 grupy badawcze
            $childA = $user->children()->firstOrCreate(['name' => 'Badanie A: Tylko Piktogramy']);
            $childB = $user->children()->firstOrCreate(['name' => 'Badanie B: Tylko Quizy']);
            $childC = $user->children()->firstOrCreate(['name' => 'Badanie C: Piktogramy + Quizy']);

            // Czyścimy stare dane
            foreach([$childA, $childB, $childC] as $child) {
                $child->sentenceLogs()->delete();
                $child->quizResults()->delete();
                \App\Models\ClickLog::where('child_id', $child->id)->delete();
            }

            // Generujemy 30 dni danych
            for ($i = 30; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);

                // --- GRUPA A (Tylko Piktogramy - powolny rozwój) ---
                $mluA = 1.0 + ((30 - $i) * 0.015) + (rand(-1, 1) * 0.1);
                $this->createLogs($childA, $mluA, false, $date, $pictogramIds);

                // --- GRUPA B (Tylko Quizy - średni rozwój, brakuje praktyki) ---
                $mluB = 1.0 + ((30 - $i) * 0.04) + (rand(-1, 1) * 0.1);
                $this->createLogs($childB, $mluB, true, $date, $pictogramIds);

                // --- GRUPA C (Synergia: Piktogramy + Quizy - najszybszy rozwój) ---
                $mluC = 1.0 + ((30 - $i) * 0.08) + (rand(-1, 1) * 0.1);
                $this->createLogs($childC, $mluC, true, $date, $pictogramIds);
            }
        }
        $this->command->info('Dane dla 3 grup badawczych (z kliknięciami) wygenerowane!');
    }

    private function createLogs($child, $mlu, $doQuiz, $date, $pictogramIds)
    {
        // Generujemy logi zdań (długość zdania)
        $length = max(1, rand((int)floor($mlu), (int)ceil($mlu)));

        $child->sentenceLogs()->create([
            'length' => $length,
            'pictogram_ids' => [$pictogramIds[0]], // Techniczne zaplecze
            'created_at' => $date,
            'updated_at' => $date
        ]);

        // NAPRAWA WYKRESU KOŁOWEGO: Generujemy pojedyncze "kliknięcia" w słowa
        for ($j = 0; $j < $length + rand(1, 3); $j++) {
            \App\Models\ClickLog::create([
                'child_id' => $child->id,
                'pictogram_id' => $pictogramIds[array_rand($pictogramIds)],
                'created_at' => $date,
                'updated_at' => $date
            ]);
        }

        // Generowanie wyników quizu, jeśli dana grupa je robi
        if ($doQuiz) {
            $child->quizResults()->create([
                'score' => rand(5, 10),
                'total_questions' => 10,
                'created_at' => $date,
                'updated_at' => $date
            ]);
        }
    }
}
