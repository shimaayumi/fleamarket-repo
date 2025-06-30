<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Comment;

use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
   

    public function store(CommentRequest $request, $item_id)
    {
     
        // ログインユーザーを取得
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login'); // ログインしていない場合はログインページにリダイレクト
        }

        $validated = $request->validated(); // ← これが必要！

        // コメントを保存
        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
            'comment' => $validated['comment'],
        ]);


        return redirect()->route('item.show', $item_id)->with('success', 'コメントを投稿しました！');
    }

    // コメント数をカウントするAPI
    public function count(Item $item)
    {
        return response()->json(['commentCount' => $item->comments()->count()]);
    }
}