<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $auxReply = rand(1, 10);
        $father_id = null;

        if (Comment::count()>1) {
            if($auxReply < 4){
                $father_id = $this->faker->numberBetween(1, Comment::count());
            }
        }

        $auxActive = rand(1, 12);
        $active = false;

        if($auxActive > 1){
            $active = true;
        }

        return [
            'user_id' => $this->faker->numberBetween(1, User::count()),
            'post_id' => $this->faker->numberBetween(1, Post::count()),
            'content' => $this->faker->text(),
            'father_id' => $father_id,
            'active' => $active,
        ];
    }
}
