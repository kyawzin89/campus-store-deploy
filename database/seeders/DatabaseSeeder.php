<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Builds a demo database: php artisan migrate:fresh --seed
     *
     * Demo login: demo@example.com / password
     */
    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'password',
        ]);

        Note::factory()->count(12)->for($demo)->create();

        // A few other users with their own notes, to check that
        // users cannot see each other's data.
        User::factory()
            ->count(3)
            ->has(Note::factory()->count(5))
            ->create();
    }
}
