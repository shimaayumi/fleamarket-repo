<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Address;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Mockery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;
    //7.商品詳細情報取得 必要な情報が表示される（商品画像、商品名、ブランド名、価格、いいね数、コメント数、商品説明、商品情報（カテゴリ、商品の状態）、コメント数、コメントしたユーザー情報、コメント内容）
    public function test_product_detail_displays_all_required_information()
    {
        $user = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'item_name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'price' => 12345,
            'description' => 'これはテスト商品の説明です。',
            'status' => '良好',  // 正しいstatus値を使用
        ]);

        Item::factory()->create([
            'categories' => json_encode(['家電', 'スマホ'])  // JSON配列として保存
        ]);

        $comments = Comment::factory()->count(2)->create([
            'item_id' => $item->id,
        ]);
        $likes = Like::factory()->count(3)->create([
            'item_id' => $item->id,
        ]);

        $response = $this->get('/item/' . $item->id);

        $response->assertSee($item->item_name);
        $response->assertSee($item->brand_name);
        $response->assertSee(number_format($item->price));
        $response->assertSee($item->description);
        $response->assertSee('良好'); // ステータス表示名（日本語）でチェックする



        //7.商品詳細情報取得 複数選択されたカテゴリが表示されているか
        foreach (json_decode($item->categories) as $category) {
            $response->assertSee($category);
        }

        $response->assertSee((string) $likes->count());
        $response->assertSee((string) $comments->count());

        foreach ($comments as $comment) {
            $response->assertSee($comment->user->name);
            $response->assertSee($comment->comment);
        }
    }


    //8.いいね機能 いいねアイコンを押下することによって、いいねした商品として登録することができる。
    public function test_user_can_like_item()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // ユーザーでログイン
        $this->actingAs($user);

        // 商品詳細ページにアクセス
        $response = $this->get(route('item.show', $item->id));

        // いいねアイコンが表示されていることを確認
        $response->assertSee('☆'); // 初期状態のアイコン（未いいね）

        // いいねアイコンを押下するためのリクエスト
        $response = $this->post(route('items.toggleLike', $item->id));

        // いいね合計値が増加したことを確認
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // レスポンスのisLikedフラグがtrueに変更されたことを確認
        $response->assertJson([
            'isLiked' => true,
        ]);
    }

    //8.いいね機能  追加済みのアイコンは色が変化する
    public function test_like_icon_changes_color_when_liked()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // ユーザーでログイン
        $this->actingAs($user);

        // 商品詳細ページにアクセス
        $response = $this->get(route('item.show', $item->id));

        // いいねアイコンが未押下状態で表示されていることを確認
        $response->assertSee('☆'); // 初期状態のアイコン（未いいね）

        // いいねアイコンを押下するためのリクエスト
        $response = $this->post(route('items.toggleLike', $item->id));

        // アイコンが押下された状態で色が変化していることを確認
        $response->assertJson([
            'isLiked' => true,  // アイコンがいいねされている状態（色が変わる）
        ]);
    }



    //8.いいね機能  再度いいねアイコンを押下することによって、いいねを解除することができる。
    public function test_user_can_unlike_item()
    {
        // ユーザーと商品を作成
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // ユーザーでログイン
        $this->actingAs($user);

        // 商品詳細ページにアクセス
        $response = $this->get(route('item.show', $item->id));

        // いいねアイコンを押下して、いいねを登録
        $this->post(route('items.toggleLike', $item->id));

        // いいねが登録された後のアイコンと合計値を確認
        $response = $this->get(route('item.show', $item->id));
        $response->assertSee('★'); // いいねされたアイコン（色が変化）

        // いいねアイコンを再度押下して、いいねを解除
        $this->post(route('items.toggleLike', $item->id));

        // いいねが解除された後のアイコンと合計値を確認
        $response = $this->get(route('item.show', $item->id));
        $response->assertSee('☆'); // いいね解除後のアイコン（色が元に戻る）
    }

    //9.コメント送信機能 ログイン済みのユーザーはコメントを送信できる
    public function test_logged_in_user_can_submit_comment()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(); // 商品を作成

        // ユーザーでログイン
        $this->actingAs($user);

        // コメントを送信
        $response = $this->post(route('items.comment', $item->id), [
            'comment' => 'This is a test comment.',
        ]);

        // コメントがデータベースに保存され、コメント数が増加したことを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'comment' => 'This is a test comment.',
        ]);

        $response->assertRedirect(route('item.show', $item->id)); // コメント送信後に商品ページにリダイレクトされることを確認
    }

    //9.コメント送信機能 ログイン前のユーザーはコメントを送信できない
    public function test_guest_user_cannot_submit_comment()
    {
        $item = Item::factory()->create(); // 商品を作成

        // ログインせずにコメントを送信
        $response = $this->post(route('items.comment', $item->id), [
            'comment' => 'This is a test comment.',
        ]);

        // ゲストユーザーはコメント送信できないので、ログインページにリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }


    //9.コメント送信機能 コメントが入力されていない場合、バリデーションメッセージが表示される
    public function test_validation_message_is_displayed_when_comment_is_empty()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(); // 商品を作成

        // ユーザーでログイン
        $this->actingAs($user);

        // コメントなしで送信
        $response = $this->post(route('items.comment', $item->id), [
            'comment' => '',
        ]);

        // バリデーションエラーメッセージが表示されることを確認
        $response->assertSessionHasErrors('comment');
    }

    //9.コメント送信機能 コメントが255字以上の場合、バリデーションメッセージが表示される
    public function test_validation_message_is_displayed_when_comment_is_too_long()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(); // 商品を作成

        // ユーザーでログイン
        $this->actingAs($user);

        // 256文字のコメントを送信
        $response = $this->post(route('items.comment', $item->id), [
            'comment' => str_repeat('a', 256), // 256文字
        ]);

        // バリデーションエラーメッセージが表示されることを確認
        $response->assertSessionHasErrors('comment');
    }


    
}
