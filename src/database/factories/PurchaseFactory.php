<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            'address_id' => Address::factory(),
            'price' => $this->faker->numberBetween(1000, 10000),
            'shipping_postal_code' => $this->faker->postcode,
            'shipping_address' => $this->faker->address, 
            'shipping_building' => $this->faker->buildingNumber,
         
        ];
    }
}
