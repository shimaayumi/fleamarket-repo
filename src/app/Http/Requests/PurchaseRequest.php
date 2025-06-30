<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PurchaseRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを実行する権限があるかを判断します。
     *
     * @return bool
     */
    public function authorize()
    {
        // 購入フォームのリクエストは、全てのユーザーに許可
        return true;
    }

    /**
     * リクエストに適用するバリデーションルールを取得します。
     *
     * @return array
     */
        public function rules()
        {
            return [
            'payment_method' => 'required|in:convenience_store,credit_card',
            'address.postal_code' => 'required|string',
            'address.address' => 'required|string',
            'address.building' => 'required|string',
            ];
        }

        public function messages()
        {
            return [
                'payment_method.required' => '支払い方法は必須です',
                'address.postal_code.required' => '郵便番号は必須です',
                'address.address.required' => '住所は必須です',
                'address.building.required' => '建物名は必須です',
            ];
        }

        /**
         * バリデーション後に使用するデータを取得します。
         *
         * @return array
         */
        public function validated()
        {
            return parent::validated();
        }

 

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'errors' => $validator->errors()
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
    }
