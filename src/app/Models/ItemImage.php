<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    use HasFactory;

    protected $table = 'item_images'; // 明示的にテーブル名を指定（省略可）

    protected $fillable = [
        'item_id',
        'item_image', // マイグレーションのカラム名に合わせる
    ];

    /**
     * 商品に関連する画像
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
