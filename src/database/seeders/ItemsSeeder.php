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
        $category = Category::first();
        $userA = User::where('email', 'userA@example.com')->first();
        $userB = User::where('email', 'userB@example.com')->first();

        $items = [
            [
                'user' => $userA,
                'item_name' => '腕時計',
                'price' => 15000,
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'item_image' => 'Armani+Mens+Clock.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'status' => '良好',
            ],
            [
                'user' => $userA,
                'item_name' => 'HDD',
                'price' => 5000,
                'description' => '高速で信頼性の高いハードディスク',
                'item_image' => 'HDD+Hard+Disk.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'status' => '目立った傷や汚れなし',
            ],
            [
                'user' => $userA,
                'item_name' => '玉ねぎ3束',
                'price' => 300,
                'description' => '新鮮な玉ねぎ3束のセット',
                'item_image' => 'iLoveIMG+d.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'status' => 'やや傷や汚れあり',
            ],
            [
                'user' => $userA,
                'item_name' => '革靴',
                'price' => 4000,
                'description' => 'クラシックなデザインの革靴',
                'item_image' => 'Leather+Shoes+Product+Photo.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'status' => '状態が悪い',
            ],
            [
                'user' => $userA,
                'item_name' => 'ノートPC',
                'price' => 45000,
                'description' => '高性能なノートパソコン',
                'item_image' => 'Living+Room+Laptop.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'status' => '良好',
            ],
            [
                'user' => $userB,
                'item_name' => 'マイク',
                'price' => 8000,
                'description' => '高音質のレコーディング用マイク',
                'item_image' => 'Music+Mic+4632231.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'status' => '目立った傷や汚れなし',
            ],
            [
                'user' => $userB,
                'item_name' => 'ショルダーバッグ',
                'price' => 3500,
                'description' => 'おしゃれなショルダーバッグ',
                'item_image' => 'Purse+fashion+pocket.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'status' => 'やや傷や汚れあり',
            ],
            [
                'user' => $userB,
                'item_name' => 'タンブラー',
                'price' => 500,
                'description' => '使いやすいタンブラー',
                'item_image' => 'Tumbler+souvenir.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'status' => '状態が悪い',
            ],
            [
                'user' => $userB,
                'item_name' => 'コーヒーミル',
                'price' => 4000,
                'description' => '手動のコーヒーミル',
                'item_image' => 'Waitress+with+Coffee+Grinder.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'status' => '良好',
            ],
            [
                'user' => $userB,
                'item_name' => 'メイクセット',
                'price' => 2500,
                'description' => '便利なメイクアップセット',
                'item_image' => '外出メイクアップセット.jpg',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'status' => '目立った傷や汚れなし',
            ],
        ];

        foreach ($items as $itemData) {
            // 商品をItemテーブルに保存
            $item = Item::create([
                'item_name' => $itemData['item_name'],
                'price' => $itemData['price'],
                'description' => $itemData['description'],
                'status' => $itemData['status'],
                'user_id' => $itemData['user']->id,
                'categories' => json_encode([$category->id]),
            ]);

            try {
                logger()->info("取得中: {$itemData['image_url']}");

                // curlで画像を取得
                $ch = curl_init($itemData['image_url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

                $imageContents = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode !== 200 || $imageContents === false) {
                    throw new \Exception("HTTP $httpCode で失敗");
                }

                // URLからファイル名を取得 → URLデコード（日本語に戻す）
                $encodedName = basename(parse_url($itemData['image_url'], PHP_URL_PATH));
                $imageName = urldecode($encodedName); // 例: 外出メイクアップセット.jpg

                // ファイルを保存（storage/app/public/images/）
                Storage::disk('public')->put("images/" . $imageName, $imageContents);

                // item_images テーブルに保存
                DB::table('item_images')->insert([
                    'item_id' => $item->id,
                    'item_image' => $imageName, // 公開パスとして保存
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                logger()->info("保存完了: $imageName");
            } catch (\Exception $e) {
                logger()->error("画像の保存に失敗: {$itemData['image_url']} - {$e->getMessage()}");
            }
        }
    }
}
