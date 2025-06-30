<?php
namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use App\Models\Purchase;
use App\Models\Item;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Mockery;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;



class PurchaseTest extends TestCase

{

use RefreshDatabase;

    //10.商品購入機能「購入する」ボタンを押下すると購入が完了する

    public function test_user_can_complete_purchase()
    {
        // ユーザーを作成し、プロフィールと住所も関連付ける
        $user = \App\Models\User::factory()
            ->has(Profile::factory()->has(Address::factory()))  // プロフィールと住所を作成
            ->create();

        // アイテムを作成
        $item = Item::factory()->create([
            'user_id' => User::factory()->create()->id,  // アイテムの出品者を指定
            'price' => 1000,
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // 購入に必要なリクエストデータ
        $requestData = [
            'payment_method' => 'credit_card',
            'address' => [
                'postal_code' => '123-4567',
                'address' => '東京都千代田区テスト町',
                'building' => 'テストビル201',
            ],
        ];

        // 購入処理のリクエストを送信
        $response = $this->postJson(
            route('purchase.checkout', ['item_id' => $item->id]),
            $requestData
        );

        // レスポンスステータスが200であることを確認
        $response->assertStatus(200); // JSONなので200が来るはず（リダイレクトじゃない）

        // データベースに購入情報が追加されていることを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'status' => 'pending',
        ]);
    }

    //10.商品購入機能　購入した商品は商品一覧画面にて「sold」と表示される
    public function test_sold_item_is_displayed_with_sold_label()
    {

        $item = Item::factory()->create([
            'sold_flag' => 1, // すでに購入済みにしておく
        ]);
        $response = $this->get(route('index')); // 商品一覧ページ
        $response->assertStatus(200);
        $response->assertSee('sold'); // sold 表示があるか確認

    }

    //10.商品購入機能「プロフィール/購入した商品一覧」に追加されている
    public function test_updated_shipping_address_is_reflected_on_purchase_page()
    {
        try {
            $user = User::factory()->create([
                'email_verified_at' => now(),
            ]);

            $item = Item::factory()->create();
            $purchase = Purchase::factory()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'status' => 'pending',
            ]);

            $this->actingAs($user);

            // 住所更新リクエスト送信（仮にupdateがPOSTの場合）
            $response = $this
                ->withSession(['purchase_id' => $purchase->id])
                ->put("/purchase/address/{$purchase->item_id}", [
                    'payment_method' => 'credit_card',
                    'postal_code' => '123-4567',
                    'address' => '東京都渋谷区',
                    'building' => 'ヒカリエ10F',
                ]);

            $response->assertStatus(302); // リダイレクトなどが起きる想定なら200ではなく302

            // 再度購入ページにアクセスして住所が反映されているか確認
            $response = $this->get("/purchase/{$item->id}");

            $response->assertStatus(200);
            $response->assertSeeText('123-4567');
            $response->assertSeeText('東京都渋谷区');
            $response->assertSeeText('ヒカリエ10F');
        } catch (\Exception $e) {
            \Log::error('Test error: ' . $e->getMessage());
            $this->fail($e->getMessage());
        }
}
}