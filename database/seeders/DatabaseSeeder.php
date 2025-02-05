<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаем 20 тегов для использования
        $tags = Tag::factory(20)->create();

        // Создаем 15 пользователей
        for ($i = 1; $i <= 15; $i++) {
            $user = User::factory()->create([
                'email' => "example{$i}@mail.com",
            ]);

            // Создаем 15 постов для каждого пользователя
            Post::factory(15)
                ->create(['user_id' => $user->id])
                ->each(function ($post) use ($tags) {
                    // Привязываем от 1 до 4 случайных тегов к посту
                    $post->tags()->attach(
                        $tags->random(rand(1, 4))->pluck('id')->toArray()
                    );
                });
        }
    }
}
