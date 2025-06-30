<?php

namespace App\Http\Controllers;


use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Http\Requests\ChatRequest;
use Illuminate\Support\Facades\Mail;



use App\Models\Purchase;


class ChatController extends Controller
{
    // 取引中商品の一覧を表示
    public function show(Purchase $purchase)
    {
        $userId = auth()->id();
        $isSelfSeller = auth()->id() === $purchase->item->user_id;
        $chatPartner = $isSelfSeller ? $purchase->user : $purchase->item->user;
        // 購入者または出品者のみアクセス可能
        if ($purchase->user_id !== $userId && $purchase->item->user_id !== $userId) {
            abort(403, 'この取引にはアクセスできません');
        }

        // チャットメッセージを取得（古い順）
        $messages = $purchase->messages()->with('user')->orderBy('created_at')->get();

        // 取引中の商品一覧を取得（新規メッセージの未読数付きで）
        $tradingItems = Purchase::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereHas('item', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
        })
            ->where('status', 'in_progress') // 取引中のみ
            ->withCount(['messages as unread_count' => function ($query) use ($userId) {
                $query->where('is_read', false)->where('user_id', '<>', $userId);
            }])
            ->orderByDesc('updated_at')
            ->get();

        return view('chat', compact('purchase', 'messages', 'tradingItems' ,'chatPartner'));
    }


    public function store(ChatRequest $request, Purchase $purchase)
    {

        $messageText = trim($request->input('message'));

        if ($messageText === '' && !$request->hasFile('image')) {
            return back()->withErrors(['message' => 'メッセージまたは画像を入力してください']);
        }

        $message = new Chat();
        $message->purchase_id = $purchase->id;
        $message->user_id = Auth::id();
        $message->content = $messageText;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/chat_images');
            $message->image_path = basename($path);
        }

        $message->save();

        $purchase->touch();

        return redirect()->back();
    }


    public function destroy(Chat $chat)
    {
        $this->authorize('delete', $chat);
        $chat->delete();

        return back()->with('success', 'メッセージを削除しました。');
    }


    public function complete(Request $request, Purchase $purchase)
    {
        $userId = auth()->id();

        if ($purchase->user_id !== $userId && $purchase->item->user_id !== $userId) {
            abort(403, 'この取引を完了する権限がありません。');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // すでに評価済みか確認
        $alreadyReviewed = Review::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $userId)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'すでにレビュー済みです。');
        }

        // 出品者がレビューする場合 → 購入者のレビューがあるか確認
        $isSeller = $userId === $purchase->item->user_id;
        if ($isSeller) {
            $buyerReviewed = Review::where('purchase_id', $purchase->id)
                ->where('reviewer_id', $purchase->user_id)
                ->exists();

            if (!$buyerReviewed) {
                return back()->with('error', '購入者の評価が完了していないため、出品者はまだ評価できません。');
            }
        }

        // レビュー対象のユーザー
        $revieweeId = $userId === $purchase->user_id
            ? $purchase->item->user_id
            : $purchase->user_id;

        Review::create([
            'purchase_id' => $purchase->id,
            'reviewer_id' => $userId,
            'reviewee_id' => $revieweeId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // 両者がレビュー済みか確認
        $reviewerIds = Review::where('purchase_id', $purchase->id)
            ->pluck('reviewer_id')
            ->unique();

        $bothReviewed = $reviewerIds->contains($purchase->user_id)
            && $reviewerIds->contains($purchase->item->user_id);

        // 出品者が評価して、かつ両者評価済みのとき → 完了 + メール
        if ($isSeller && $bothReviewed) {
            $purchase->status = 'completed';
            $purchase->save();

            
            // メール送信
            Mail::to($purchase->item->user->email)
            ->send(new \App\Mail\PurchaseCompleteMail($purchase));
        }

        return redirect()->route('index')->with('success', '評価を送信しました。');
    }



    public function update(Request $request, Chat $chat)
    {
        $this->authorize('update', $chat); // ポリシー適用

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $chat->content = $request->input('content');
        $chat->save();

        return redirect()->back()->with('success', 'メッセージを更新しました');
    }
}

