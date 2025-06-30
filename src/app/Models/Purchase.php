<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\PaymentIntent;

class Purchase extends Model
{
    use HasFactory;

    
        
    protected $fillable = [
        'user_id',
        'item_id',
        'address_id',
        'payment_method',
        'price',
        'status',
        'stripe_session_id',
        'shipping_postal_code',
        'shipping_address',
        'shipping_building',
        'stripe_payment_intent_id',
    ];

    /**
     * 購入者（ユーザー）
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 購入された商品
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Chat::class); // ←チャット機能がこのモデルなら
    }
    /**
     * 購入時の住所
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function getStripeStatus()
    {
        if (!$this->stripe_session_id) {
            return '未決済';
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = StripeSession::retrieve([
                'id' => $this->stripe_session_id,
                'expand' => ['payment_intent'],
            ]);

            // セッションから PaymentIntent ID を取得
            if (!empty($session->payment_intent)) {
                $paymentIntent = PaymentIntent::retrieve($session->payment_intent);
                return $paymentIntent->status; // 例: 'succeeded', 'requires_payment_method', etc.
            } else {
                return '支払い情報なし';
            }
        } catch (\Exception $e) {
            \Log::error('Stripe API エラー: ' . $e->getMessage());
            return '取得失敗';
        }
    }


    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 購入者・出品者両方が評価済みかを判定
     */
    public function isBothReviewed(): bool
    {
        // 2件以上レビューがあれば両者評価済みとみなす
        return $this->reviews()->count() >= 2;
    }


    public function latestMessage()
    {
        return $this->hasOne(Chat::class, 'purchase_id', 'id')->latestOfMany();
    }   
}


