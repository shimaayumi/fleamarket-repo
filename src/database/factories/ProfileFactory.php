<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Address; // Addressモデルをインポート
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(), // Userのファクトリを使用

        
            'profile_image' => $this->faker->imageUrl, // プロフィール画像のダミーURL

          
        ];
    }
}
