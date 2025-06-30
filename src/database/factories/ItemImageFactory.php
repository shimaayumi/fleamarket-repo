<?php

namespace Database\Factories;

use App\Models\ItemImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemImageFactory extends Factory
{
    protected $model = ItemImage::class;

    public function definition()
    {
        return [
            'item_id' => \App\Models\Item::factory(),
            'image_path' => $this->faker->imageUrl(),
        ];
    }
}
