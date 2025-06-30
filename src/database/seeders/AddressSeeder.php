<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Address;


class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(3); // ユーザーIDが3のユーザーを取得
        $address = Address::find(2); // アドレスIDが2のアドレスを取得

        if ($user && $address) {
            $address->user_id = $user->id; // ユーザーIDを設定
            $address->save(); // アドレスを保存
        }
    }
}
