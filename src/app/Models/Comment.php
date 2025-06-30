<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',    // ユーザーID
        'item_id',    // 商品ID
        'comment'     // コメント内容
    ];

    /**
     * コメントを投稿したユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * コメントが投稿された商品
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
