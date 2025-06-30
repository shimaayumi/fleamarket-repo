<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを実行する権限があるかを判断します。
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // 全ユーザーがリクエスト可能
    }

    /**
     * リクエストに適用するバリデーションルールを取得します。
     *
     * @return array
     */
    public function rules()
    {
        return [
            'item_name' => 'required|string',
            'description' => 'required|string|max:255',
            'item_image' => 'required',
            'item_image.*' => 'image|mimes:jpeg,png',
            'category_id' => 'required',
            'status' => 'required|string',
            'price' => 'required|integer|min:0',
            
        ];
    }

    /**
     * バリデーションエラーメッセージをカスタマイズする。
     *
     * @return array
     */
    public function messages()
    {
        return [
            'item_name.required' => '商品名を入力してください',
            'description.required' => '商品の説明を入力してください',
            'description.max' => '商品の説明は255文字以内で入力してください',
            'category_id.required' => 'カテゴリーを選択してください',
            'status.required' => '商品の状態を選択してください',
            'price.required' => '販売価格を入力してください',
            'price.min' => '販売価格は0円以上で入力してください',
            'item_image.required' => '商品画像をアップロードしてください',
            'item_image.*.image' => 'アップロードするファイルは画像である必要があります',
            'item_image.*.mimes' => '商品画像は JPEG,PNGの形式でアップロードしてください',

        ];
    }
}
