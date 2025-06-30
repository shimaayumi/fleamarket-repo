<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User; // Userモデルをインポート
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // ユーザーIDを設定
            'postal_code' => $this->faker->postcode,
            'address' => $this->faker->address,
            'building' => $this->faker->word, 
        ];
    }
}