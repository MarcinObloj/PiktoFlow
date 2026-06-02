<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->command->info('Rozpoczynam pobieranie i seedowanie rozszerzonej bazy ARASAAC (może to zająć kilka minut)...');
        Artisan::call('arasaac:fetch', [], $this->command->getOutput());
        $this->command->info('Baza piktogramów została poprawnie zainicjalizowana!');
    }
}
