<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function rules()
    {
        return [
            'profile_image' => 'nullable|mimes:jpeg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'profile_image.mimes' => 'プロフィール画像はJPEGまたはPNG形式である必要があります',
            'profile_image.max' => 'プロフィール画像は2MB以下でなければなりません',
        ];
    }
}
