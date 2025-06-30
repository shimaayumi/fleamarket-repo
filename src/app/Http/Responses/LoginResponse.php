<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // メール認証が完了していれば/mypageにリダイレクト
        if ($request->user()->hasVerifiedEmail()) {
            dd('Redirecting to /mypage');
            return redirect('/mypage');
        }

        // メール認証前の場合
        return redirect()->route('verification.notice');
    }
}