<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Round::factory(10)
            ->has(User::factory()->count(4), 'participants')
            ->has(Story::factory()
                ->has(Media::factory())
            ->count(4), 'roundStories')
            ->create();

        User::factory()->create([
            'first_name' => 'Alexandre',
            'last_name'  => 'Chauvin',
            'user_name'  => 'Alpifra',
            'email'      => 'admin@email.com',
            'password'   => Hash::make('password'),
        ]);
    }
}
