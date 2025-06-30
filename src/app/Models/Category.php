<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',  // カテゴリ名
        'description' // カテゴリ説明
    ];

    /**
     * カテゴリに関連する商品
     */
    public function items()
    {
        return $this->belongsToMany(Item::class);
    }
}