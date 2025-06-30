<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;



class UserController extends Controller
{

    // マイページの表示
    public function mypage()
    {
        $page = request()->get('page', 'sell');
        $user = auth()->user();

        $sellItems = $user->items()->with('images')->get();
        $purchasedItems = $user->purchasedItems()->with('images')->get();
        $likedItems = $user->likes()->with('item')->get();

        $tradingItems = \App\Models\Purchase::where('status', 'in_progress')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('item', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->with(['item.images', 'latestMessage']) // 最新メッセージのリレーションも取得
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('is_read', false)
                    ->where('user_id', '<>', $user->id);
            }])
            ->get()
            ->sortByDesc(function ($purchase) {
                // 最新メッセージの日時があればそれを優先、なければpurchaseの更新日時でソート
                return optional($purchase->latestMessage)->created_at ?? $purchase->updated_at;
            })
            ->values(); // インデックスを振り直す

        // 評価の平均を取得
        $reviewCount = Review::where('reviewee_id', $user->id)->count();
        $averageRating = $reviewCount > 0
            ? round(Review::where('reviewee_id', $user->id)->avg('rating'))
            : null;

        $unreadCount = $tradingItems->sum(fn($purchase) => $purchase->unread_count ?? 0);

        return view('profile', compact(
            'page',
            'user',
            'sellItems',
            'purchasedItems',
            'likedItems',
            'tradingItems',
            'averageRating',
            'unreadCount'
        ));
    }



    public function editProfile(Request $request)
    {
        $user = auth()->user();

        // 1. AddressRequestのバリデーションを実行
        $addressValidator = Validator::make($request->all(), (new AddressRequest)->rules(), (new AddressRequest)->messages());
        if ($addressValidator->fails()) {
            return back()->withErrors($addressValidator)->withInput();
        }

        // 2. ProfileRequestのバリデーションを実行
        $profileValidator = Validator::make($request->all(), (new ProfileRequest)->rules(), (new ProfileRequest)->messages());
        if ($profileValidator->fails()) {
            return back()->withErrors($profileValidator)->withInput();
        }

        // バリデーション通過後、値を取得
        $validated = array_merge($addressValidator->validated(), $profileValidator->validated());

        // プロフィール画像の処理
        if ($request->hasFile('profile_image')) {
            $filename = $request->file('profile_image')->store('public/profiles');
            $profileImage = basename($filename);
        } else {
            $profileImage = $user->profile ? $user->profile->profile_image : 'default.png';
        }

      

        // プロフィール画像の保存または更新
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['profile_image' => $profileImage]
        );

        // 住所情報の保存または更新
        $user->address()->updateOrCreate([], [
            'postal_code' => $validated['postal_code'],
            'address' => $validated['address'],
            'building' => $validated['building'],
        ]);

        return redirect()->route('mypage', ['page' => 'sell'])->with('success', 'プロフィールが更新されました');
    }




    //プロフィール編集画面を表示
    public function edit()
    {
        $user = auth()->user(); // 現在ログイン中のユーザーを取得
        $address = $user->address;

        return view('profile_edit', compact('user', 'address'));
    }
}