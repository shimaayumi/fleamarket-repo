<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Chat extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'purchase_id',
        'user_id',
        'content',
        'is_read',
        'image_path',
    ];

    // リレーション（オプション）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
