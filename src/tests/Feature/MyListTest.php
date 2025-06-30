<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class MyListTest extends TestCase
{
    use RefreshDatabase;
    //5.マイリスト一覧取得 いいねをした商品が表示される
    public function test_liked_items_are_displayed()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->actingAs($user)->get('/?page=mylist');

        $response->assertStatus(200);
        $response->assertSee($item->item_name);
    }


    //5.マイリスト一覧取得 購入済み商品に「Sold」のラベルが表示される
    public function test_sold_label_displayed_for_purchased_items()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['sold_flag' => true]);
        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->actingAs($user)->get('/?page=mylist');

        $response->assertSee('SOLD');
    }


    //5.マイリスト一覧取得 自分が出品した商品が一覧に表示されない
    public function test_own_items_are_not_displayed_in_mylist()
    {
        $user = User::factory()->create();
        $ownItem = Item::factory()->create(['user_id' => $user->id]);
        $otherItem = Item::factory()->create();

        // 自分の商品と他人の商品の両方にいいねをつける
        Like::create(['user_id' => $user->id, 'item_id' => $ownItem->id]);
        Like::create(['user_id' => $user->id, 'item_id' => $otherItem->id]);

        $response = $this->actingAs($user)->get('/?page=mylist');

        $response->assertStatus(200);
        $response->assertSee($otherItem->item_name);
        $response->assertDontSee($ownItem->item_name);
    }

    //5.マイリスト一覧取得 未認証の場合は何も表示されない
    public function test_unauthenticated_user_cannot_see_items_on_mylist()
    {
        // 未認証ユーザーでマイリストページにアクセス
        $response = $this->get(route('mypage'));

        // 未認証ユーザーの場合、ログインページにリダイレクトされることを確認
        $response->assertRedirect(route('login'));  // ログインページにリダイレクトされることを確認

        // リダイレクトされる場所がログインページか確認
        $response->assertRedirect();
    }



    //6.商品検索機能 「商品名」で部分一致検索ができる
    public function test_product_search_returns_matching_items()
    {
        // テスト用ユーザー＆商品作成
        $user = User::factory()->create();
        Item::factory()->create(['item_name' => 'レインボーキーホルダー']);
        Item::factory()->create(['item_name' => 'サングラス']);

        // 商品名の一部「レインボー」で検索
        $response = $this->actingAs($user)->get('/?keyword=レインボー');

        $response->assertStatus(200);
        $response->assertSee('レインボーキーホルダー');
        $response->assertDontSee('サングラス');
    }


    //6.商品検索機能 検索状態がマイリストでも保持されている
    public function test_mylist_search_keeps_keyword_and_filters_items()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 他ユーザーの商品を2つ用意（うち1つはいいね）
        $matchingItem = Item::factory()->create(['item_name' => 'ハンドメイドブレスレット', 'user_id' => $otherUser->id]);
        $nonMatchingItem = Item::factory()->create(['item_name' => 'スマホケース', 'user_id' => $otherUser->id]);

        // userがmatchingItemにいいねする
        Like::factory()->create(['user_id' => $user->id, 'item_id' => $matchingItem->id]);
        Like::factory()->create(['user_id' => $user->id, 'item_id' => $nonMatchingItem->id]);

        // 「ブレスレット」でマイリスト検索
        $response = $this->actingAs($user)->get('/?page=mylist&keyword=ブレスレット');

        $response->assertStatus(200);
        $response->assertSee('ハンドメイドブレスレット');
        $response->assertDontSee('スマホケース');
        $response->assertSee('ブレスレット'); // キーワード保持の確認
    }

}
