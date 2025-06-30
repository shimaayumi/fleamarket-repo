<?php

namespace App\Http\Controllers;


use App\Models\Item;
use Illuminate\Support\Facades\Auth;



class LikeController extends Controller
{
    public function store(Item $item)
    {
      
        $user = Auth::user();

        // すでにいいねしている場合は何もしない
        if ($user->likes()->where('item_id', $item->id)->exists()) {
            return response()->json(['message' => 'すでにいいねしています'], 400);
        }

        // 新規いいね
        $user->likes()->create(['item_id' => $item->id]);

        // いいね数を返す
        return response()->json(['like_count' => $item->likeCount()]);
    }

    public function destroy(Item $item)
    {
       
        $user = Auth::user();

        // いいねを解除
        $user->likes()->where('item_id', $item->id)->delete();

        // いいね数を返す
        return response()->json(['like_count' => $item->likes->count()]);
    }

    public function toggleLike(Item $item)
    {
        $user = auth()->user();

      
        $like = $user->likes()->where('item_id', $item->id)->first();

        if ($like) {
           
            $like->delete();
            $message = 'Success';
            $likeCount = $item->likeCount(); 
            $isLiked = false; 
        } else {
           
            $user->likes()->create(['item_id' => $item->id]);
            $message = 'Success';
            $likeCount = $item->likeCount(); 
            $isLiked = true; 
        }

        return response()->json([
            'message' => $message,
            'likeCount' => $likeCount,
            'isLiked' => $isLiked, 
        ]);
    }
    
}