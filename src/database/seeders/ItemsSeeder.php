<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;

class ItemsSeeder extends Seeder
{
    public function run()
    {
        // カテゴリを取得（適宜修正してください）
        $category = Category::first();

        // ユーザーを取得
        $userA = User::where('email', 'userA@example.com')->first();
        $userB = User::where('email', 'userB@example.com')->first();

        // ユーザーAの商品データ（CO01〜CO05）
        $itemsA = [
            [
                'item_name' => '腕時計',
                'price' => 15000,
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'item_image' => 'Armani+Mens+Clock.jpg',
                'status' => '良好',
            ],
            [
                'item_name' => 'HDD',
                'price' => 5000,
                'description' => '高速で信頼性の高いハードディスク',
                'item_image' => 'HDD+Hard+Disk.jpg',
                'status' => '目立った傷や汚れなし',
            ],
            [
                'item_name' => '玉ねぎ3束',
                'price' => 300,
                'description' => '新鮮な玉ねぎ3束のセット',
                'item_image' => 'iLoveIMG+d.jpg',
                'status' => 'やや傷や汚れあり',
            ],
            [
                'item_name' => '革靴',
                'price' => 4000,
                'description' => 'クラシックなデザインの革靴',
                'item_image' => 'Leather+Shoes+Product+Photo.jpg',
                'status' => '状態が悪い',
            ],
            [
                'item_name' => 'ノートPC',
                'price' => 45000,
                'description' => '高性能なノートパソコン',
                'item_image' => 'Living+Room+Laptop.jpg',
                'status' => '良好',
            ],
        ];

        // ユーザーBの商品データ（CO06〜CO10）
        $itemsB = [
            [
                'item_name' => 'マイク',
                'price' => 8000,
                'description' => '高音質のレコーディング用マイク',
                'item_image' => 'Music+Mic+4632231.jpg',
                'status' => '目立った傷や汚れなし',
            ],
            [
                'item_name' => 'ショルダーバッグ',
                'price' => 3500,
                'description' => 'おしゃれなショルダーバッグ',
                'item_image' => 'Purse+fashion+pocket.jpg',
                'status' => 'やや傷や汚れあり',
            ],
            [
                'item_name' => 'タンブラー',
                'price' => 500,
                'description' => '使いやすいタンブラー',
                'item_image' => 'Tumbler+souvenir.jpg',
                'status' => '状態が悪い',
            ],
            [
                'item_name' => 'コーヒーミル',
                'price' => 4000,
                'description' => '手動のコーヒーミル',
                'item_image' => 'Waitress+with+Coffee+Grinder.jpg',
                'status' => '良好',
            ],
            [
                'item_name' => 'メイクセット',
                'price' => 2500,
                'description' => '便利なメイクアップセット',
                'item_image' => '外出メイクアップセット.jpg',
                'status' => '目立った傷や汚れなし',
            ]
        ];

        // 商品登録 + 画像保存を関数化
        $registerItems = function ($items, $user) use ($category) {
            foreach ($items as $itemData) {
                // 商品登録
                $item = Item::create([
                    'item_name' => $itemData['item_name'],
                    'price' => $itemData['price'],
                    'description' => $itemData['description'],
                    'status' => $itemData['status'],
                    'user_id' => $user->id,
                    'categories' => json_encode([$category->id]),
                ]);

                // 画像保存処理
                $imageUrl = 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/' . rawurlencode($itemData['item_image']);
                try {
                    $ch = curl_init($imageUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

                    $imageContents = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpCode !== 200 || $imageContents === false) {
                        throw new \Exception("HTTP $httpCode で失敗");
                    }

                    $imageName = $itemData['item_image'];
                    Storage::disk('public')->put("images/" . $imageName, $imageContents);

                    // item_images 登録
                    DB::table('item_images')->insert([
                        'item_id' => $item->id,
                        'item_image' => 'images/' . $imageName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    logger()->info("画像保存成功: $imageName");
                } catch (\Exception $e) {
                    logger()->error("画像保存失敗: {$imageUrl} - {$e->getMessage()}");

                    // エラー時でもitem_imagesに登録（URLそのまま）
                    DB::table('item_images')->insert([
                        'item_id' => $item->id,
                        'item_image' => $itemData['item_image'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        };

        $registerItems($itemsA, $userA);
        $registerItems($itemsB, $userB);
    }
}