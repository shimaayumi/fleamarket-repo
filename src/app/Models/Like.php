<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $table = 'likes'; // ここでテーブル名を指定
    protected $fillable = ['user_id', 'item_id'];

    // ユーザーと商品のリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Like モデルと商品のリレーション
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // 商品の画像を取得するメソッド
    public function images()
    {
        // 商品を通じて画像を取得
        return $this->item->images(); // Itemモデルに画像の取得メソッドを追加
    }
}
