<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Storage;

use App\Models\ItemImage;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    //4.商品一覧取得 全商品を取得できる
    public function test_全ての商品が表示される()
    {
        $items = Item::factory()->count(3)->create();

        $response = $this->get('/');

        foreach ($items as $item) {
            $response->assertSee($item->item_name);
        }
    }

    //4.商品一覧取得 購入済み商品は「Sold」と表示される
    public function test_購入済みの商品には_sold_ラベルが表示される()
    {
        $user = User::factory()->create();

        // 購入された商品を作成
        $item = Item::factory()->create([
            'sold_flag' => true,
        ]);

        $response = $this->actingAs($user)->get('/');

        // 正確にHTMLとして確認（falseを渡してHTMLタグもチェック）
        $response->assertSee('<div class="sold-label">SOLD</div>', false);
    }

    //4.商品一覧取得 自分が出品した商品が一覧に表示されない
    public function 自分が出品した商品は一覧に表示されない()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 自分の商品
        $ownItem = Item::factory()->create(['user_id' => $user->id]);
        // 他人の商品
        $otherItem = Item::factory()->create();

        $response = $this->get(route('index'));

        $response->assertDontSee($ownItem->item_name);
        $response->assertSee($otherItem->item_name);
    }
    
    //テスト用データ
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'price' => $this->faker->numberBetween(1000, 10000),
            'user_id' => User::factory(),
        ];
    }


    //15．出品商品情報登録 商品出品画面にて必要な情報が保存できること（カテゴリ、商品の状態、商品名、商品の説明、販売価格）
    public function test_item_creation_saves_required_information()
    {
        // 事前に必要なユーザーなどを作成
        $user = User::factory()->create();

        // テスト対象のアイテムデータを作成
        $item = Item::create([
            'user_id' => $user->id,
            'item_name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'description' => 'これはテスト商品です。',
            'price' => 5000,
            'status' => '良好',
            'sold_flag' => false,
            'categories' => json_encode([1]), // JSON形式でカテゴリIDを格納
        ]);

        // 基本情報の存在確認
        $this->assertDatabaseHas('items', [
            'item_name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'price' => 5000,
            'description' => 'これはテスト商品です。',
            'status' => '良好',
        ]);

        // categoriesカラムの検証
        $retrievedItem = Item::where('item_name', 'テスト商品')->first();
        $this->assertTrue(in_array(1, json_decode($retrievedItem->categories, true)));
    }
}