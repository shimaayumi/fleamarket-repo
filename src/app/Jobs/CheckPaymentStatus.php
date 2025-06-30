<?php

namespace App\Jobs;

use Stripe\StripeClient;
use App\Models\Purchase;
use Log;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable; // トレイトのインポート
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPaymentStatus implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable; // トレイトの使用

    protected $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    public function handle()
    {

        \Log::info('CheckPaymentStatus ジョブ実行', ['purchase_id' => $this->purchase->id]);
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $paymentIntent = $stripe->paymentIntents->retrieve($this->purchase->payment_intent_id);

        if ($paymentIntent->status === 'succeeded') {
            // 支払い成功の場合、購入ステータスを更新
            $this->purchase->status = 'confirmed';
            $this->purchase->save();

            // 商品の状態を「売れた」に更新
            $item = $this->purchase->item;
            $item->sold_flag = 1;
            $item->save();
        } else {
            // 支払い失敗の場合の処理（任意）
            Log::error('コンビニ決済の支払いが完了していません。購入ID: ' . $this->purchase->id);
        }
    }
}
