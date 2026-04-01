<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);

        return [
            'author_id'    => User::factory(),
            'title'        => $title,
            'slug'         => Str::slug($title) . '-' . $this->faker->unique()->randomNumber(5),
            'excerpt'      => $this->faker->paragraph(2),
            'body'         => '<p>' . implode('</p><p>', $this->faker->paragraphs(4)) . '</p>',
            'category'     => $this->faker->randomElement(['Guide', 'Actualité', 'Tutoriel', null]),
            'is_published' => false,
            'published_at' => null,
        ];
    }
}
