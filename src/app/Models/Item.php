<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    // テーブル名を指定 (省略可能: Laravelはモデル名を小文字の複数形と見なすので、'items'は省略可能)
    protected $table = 'items';

    // マスアサインメント可能な属性
    protected $fillable = [
        'user_id',
        'category_id',
        'item_name',
        'brand_name',
        'description',
        'price',
        'status',
        'sold_flag',
        'categories'
    ];

    protected $casts = [
        'sold_flag' => 'boolean',
        
    ];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class); // 一つのアイテムは一人のユーザーに属する
    }
   

    public function likeCount()
    {
        return $this->likes()->count();
    }



    public function getCategoryListAttribute()
    {
        return explode('/', $this->categories); // / で区切って配列にする
    }


    public function Images()
    {
        return $this->hasMany(ItemImage::class, 'item_id');
    }

    // 購入とのリレーション (Purchaseとの関連付け)
    public function purchases()
    {
        return $this->hasMany(Purchase::class); // アイテムは複数の購入に関連付けられる
    }

    // アイテムの状態（新品・中古・リファービッシュ）のフィルタリングを簡単にするためのメソッド
    public function getStatusLabel()
    {
        $statusLabels = [
            'good' => '良好',
            'no_damage' => '目立った傷や汚れなし',
            'slight_damage' => 'やや傷や汚れあり',
            'bad_condition' => '状態が悪い',
        ];

        return $statusLabels[$this->status] ?? '不明';
    }

 


    public function comments()
    {
        return $this->hasMany(Comment::class); // Itemは多くのコメントを持つ
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'item_id'); // 'likes' ではなく 'item_likes' を参照
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
    
}