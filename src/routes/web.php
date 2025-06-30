<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TradeController;








Route::post('/register', [RegisterController::class, 'store']);
// メール認証ページの表示

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::get('/email/verify', [EmailVerificationController::class, 'show'])->middleware('auth')->name('verification.notice');

// メール認証処理
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// メール認証再送信
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');



// --- 商品関連 ---

Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show'); // 商品詳細
Route::match(['get', 'post'], '/', [ItemController::class, 'index'])->name('index');




// 商品出品・編集・削除（認証ユーザー専用）
Route::middleware('auth')->group(function () {
    Route::get('/sell', [ItemController::class, 'create'])->name('sell'); // 出品ページ
    Route::post('/items', [ItemController::class, 'store'])->name('items.store'); // 商品登録
    Route::get('/items/{item_id}/edit', [ItemController::class, 'edit'])->name('items.edit'); // 商品編集ページ
    Route::post('/items/{item_id}/update', [ItemController::class, 'update'])->name('items.update'); // 商品更新
    Route::post('/items/{item_id}/delete', [ItemController::class, 'destroy'])->name('items.delete'); // 商品削除


});




Route::middleware(['auth'])->group(
    function () {
        // マイページ表示
        Route::get('/mypage', [UserController::class, 'mypage'])->name('mypage');

        // プロフィール編集フォーム表示
        Route::get('/mypage/profile', [UserController::class, 'edit'])->name('edit');

        // プロフィール更新処理
        Route::post('/mypage/profile', [UserController::class, 'editProfile'])->name('edit.Profile');
    }
);

Route::middleware(['auth'])->group(function () {
    Route::get('/purchase/success', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/cancel', [PurchaseController::class, 'cancel'])->name('purchase.cancel');
    Route::get('/purchase/complete', [PurchaseController::class, 'complete'])->name('purchase.complete');
    Route::get('purchase/failed', [PurchaseController::class, 'failed'])->name('purchase.failed');
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');
    Route::post('/purchase/{item_id}/checkout', [PurchaseController::class, 'checkout'])->name('purchase.checkout');
    
   
    
});




Route::post('/items/{id}/comment', [CommentController::class, 'store'])->name('items.comment');
Route::get('/items/{item}/comment-count', [CommentController::class, 'count'])->name('items.comment.count');





// アイテムに対する「いいね」を追加または削除するためのルート
Route::middleware(['auth'])->group(function () {
    // アイテムに「いいね」を追加する
    Route::post('/item/{item}/like', [LikeController::class, 'store'])->name('item.like');

    // アイテムから「いいね」を削除する
    Route::delete('/item/{item}/like', [LikeController::class, 'destroy'])->name('item.unlike');

    // ユーザーの「いいね」状態をトグルする
    Route::post('/toggle-like/{item}', [LikeController::class, 'toggleLike'])->name('items.toggleLike');
});

Route::middleware(['auth'])->group(function () {
    // 編集画面を表示
    Route::get('/purchase/address/{item_id}', [AddressController::class, 'edit'])->name('address.edit');
    // 編集結果を保存
   
    Route::put('/purchase/address/{item_id}', [AddressController::class, 'update'])->name('address.update');
});

Route::post('/purchase/confirm/{item}', [PurchaseController::class, 'confirmPurchaseClient'])
    ->name('purchase.confirm.client');



// --- 取引チャット機能 ---

// 取引チャット画面（選択した商品）
Route::get('/mypage/chat/{purchase}', [ChatController::class, 'show'])->name('chat.show'); 

// チャット送信
Route::post('/mypage/chat/{purchase}/message', [ChatController::class, 'store'])->name('chat.message.store');


Route::post('/mypage/chat/{purchase}/complete', [ChatController::class, 'complete'])->name('chat.complete');
Route::put('/chat/message/{chat}', [ChatController::class, 'update'])->name('chat.message.update');
Route::delete('/mypage/chat/{chat}', [ChatController::class, 'destroy'])->name('chat.message.destroy');


Route::post('/purchase/complete/{purchase}', [PurchaseController::class, 'completePurchase'])
    ->name('purchase.complete')
    ->middleware('auth');