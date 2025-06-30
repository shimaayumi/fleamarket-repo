<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Item;
use App\Models\Address;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_can_view_profile_with_correct_information()
    {
        //13．ユーザー情報取得　必要な情報が取得できる（プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧）
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'profile_image' => 'profile_image.jpg',  // プロフィール画像
        ]);

        $item1 = Item::factory()->create([
            'user_id' => $user->id,
            'item_name' => '出品商品1',
        
        ]);

        $item2 = Item::factory()->create([
            'user_id' => $user->id,
            'item_name' => '出品商品2',
           
        ]);

        $purchase = Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item1->id,  // 購入した商品
            'payment_method' => 'credit_card',
        ]);

        // ユーザー情報ページにアクセス
        $response = $this->actingAs($user)->get(route('mypage', $user->id));

        // 必要な情報が正しく表示されることを確認
        $response->assertSee($user->name);  // ユーザー名
        $response->assertSee($user->profile_images);  // プロフィール画像
        $response->assertSee($item1->item_name);  // 出品した商品1
        $response->assertSee($item2->item_name);  // 出品した商品2
        $response->assertSee($purchase->item->item_name);  // 購入した商品
      
    }

    public function test_user_profile_is_preloaded_with_correct_information()
    {
        //14.ユーザー情報変更 変更項目が初期値として過去設定されていること（プロフィール画像、ユーザー名、郵便番号、住所）
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'profile_image' => 'path/to/image.jpg',
        ]);

        $user->address()->create([
            'postal_code' => '123-4567',
            'address' => '東京都新宿区1-2-3',
            'building' => 'コーチテックビル',
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // プロフィール編集ページにアクセス
        $response = $this->get(route('edit'));

        // フォームに事前に入力されている値が正しいか確認
        $response->assertSee($user->name);
        $response->assertSee($user->profile_images);  // プロフィール画像のパス
        $response->assertSee($user->postal_code);
        $response->assertSee($user->address->address); // 住所
        $response->assertSee($user->address->building); 
    }


    
}

