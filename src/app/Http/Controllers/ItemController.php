<?php

namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Models\Item;
    use App\Models\Category;
    use App\Models\ItemImage;
    use Illuminate\Support\Facades\Auth;
    use App\Http\Requests\ExhibitionRequest;
    use Illuminate\Support\Facades\DB;



    class ItemController extends Controller
    {
        // --- 共通処理 ---


        private function getCategories(Request $request)
        {
            if ($request->category_id) {
                $categories = Category::whereIn('id', explode(',', $request->category_id))->get();
            } else {
                $categories = Category::all();
            }

            return $categories;
        }

    public function index(Request $request)
    {
       
        $keyword = $request->input('keyword');
        $page = $request->input('page', 'all');
      

        if ($page === 'mylist') {
            if (!Auth::check()) {
                return view('index', [
                    'items' => collect(),
                    'categories' => $this->getCategories($request),
                    'page' => $page,
                    'keyword' => $keyword,
                ]);
            }

            $user = Auth::user();

            $likedItems = $user->likes()
                ->with('item.images')
                ->get()
                ->pluck('item')
                ->filter(function ($item) use ($user) {
                    return $item && $item->user_id !== $user->id; // null除外 ＋ 自分が出品した商品を除外
                })
                ->groupBy('id') // item_id でグルーピング
                ->map(fn($group) => $group->first()) // 最初の1つだけ使う
                ->values(); // 再インデックス化

            // 🔽🔽🔽 キーワードでフィルタ
            if ($keyword) {
                $likedItems = $likedItems->filter(function ($item) use ($keyword) {
                    return mb_stripos($item->item_name, $keyword) !== false;
                })->values();
            }

            foreach ($likedItems as $item) {
                $item->sold_image = $item->sold_flag ? asset('images/sold.png') : asset('images/available.png');
            }

            return view('index', [
                'items' => $likedItems,
                'categories' => $this->getCategories($request),
                'page' => $page,
                'keyword' => $keyword,
            ]);
        } else {
            // 'mylist' 以外の場合、すべての商品を表示
            $query = Item::query();

            // キーワードでフィルタ
            if ($keyword) {
                $query->where('item_name', 'like', "%{$keyword}%");
            }

            // ログインユーザーが出品した商品を除外
            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }

            $items = $query->with('images')->get();

            foreach ($items as $item) {
                $item->sold_image = $item->sold_flag ? asset('images/sold.png') : asset('images/available.png');
            }

            return view('index', [
                'items' => $items,
                'categories' => $this->getCategories($request),
                'page' => $page,
                'keyword' => $keyword,
            ]);
        }
    }

        // --- 商品詳細表示 ---

        public function show($id)
        {
            // 商品情報をIDで取得、存在しない場合は404エラーを返す
            $item = Item::findOrFail($id);

            // 商品に関連するコメントとユーザー情報を取得
            $comments = $item->comments()->with('user')->get();

            // 商品に紐づく画像を取得（item_imagesテーブルの画像データ）
        
            $images = $item->images ?? collect();

            // 現在ログインしているユーザーを取得
            $user = auth()->user();

            // JSONデータを配列に変換
        
            $categoryIds = json_decode($item->categories, true) ?? [];
        
            // カテゴリIDに基づいてカテゴリ情報を取得
            $categories = Category::whereIn('id', $categoryIds)->get();

            // 商品詳細ビューにデータを渡す
            return view('show', compact('item', 'comments', 'images', 'user', 'categories'));
        }

        public function create()
        {
            return view('create', [
                'categories' => Category::all(),
            ]);
        }

        
        // --- 商品出品画面表示 ---
        public function store(ExhibitionRequest $request)
        {
            // リクエストのバリデーション
            $validated = $request->validated();

            DB::transaction(function () use ($request) {
                // 商品を作成
                $item = new Item();
                $item->user_id = Auth::id();
                $item->item_name = $request->item_name;
                $item->price = $request->price;
                $item->description = $request->description;
                $item->brand_name = $request->brand_name;
                $item->sold_flag = 0; // 出品時は未販売
                $item->categories = json_encode($request->category_id); // 複数カテゴリ選択の場合はJSONで保存
                
                $item->save();

                // 画像保存
                if ($request->hasFile('item_image')) {
                    $image = $request->file('item_image');

                    // 元のファイル名を取得
                    $originalFileName = $image->getClientOriginalName();

                    // images/ディレクトリ内に元のファイル名で保存
                    $path = $image->storeAs('images', $originalFileName, 'public');

                    // 画像情報をデータベースに保存
                    ItemImage::create([
                        'item_id' => $item->id,
                        'item_image' => $originalFileName, // 元のファイル名を保存（images/なし）
                    ]);
                }
            });

        return redirect()->route('mypage', ['page' => 'sell'])
            ->with('success', '商品が出品されました！');
        }

    public function mypage(Request $request)
    {
        $page = $request->query('page', 'sell');
        $userId = Auth::id();

        $sellItems = collect();
        $buyItems = collect();
        $tradingItems = collect();

        if ($page === 'sell') {
            $sellItems = Purchase::whereHas('item', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->with(['item.images', 'messages' => fn($q) => $q->latest()->limit(1)])
                ->orderByDesc('updated_at')
                ->get();
        }

        if ($page === 'buy') {
            $buyItems = Purchase::where('user_id', $userId)
                ->where('status', 'in_progress')
                ->with(['item.images', 'messages' => fn($q) => $q->latest()->limit(1)])
                ->orderByDesc('updated_at')
                ->get();
        }

        if ($page === 'trade') {
            $tradingItems = Purchase::where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('item', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
                ->where('status', 'in_progress')
                ->with([
                    'item.images',
                    'latestMessage',
                ])
                ->withCount([
                    'messages as unread_count' => function ($query) use ($userId) {
                        $query->where('is_read', false)->where('user_id', '<>', $userId);
                    }
                ])
                ->get()
                ->sortByDesc(fn($purchase) => optional($purchase->latestMessage)->created_at ?? $purchase->updated_at)
                ->values();
            foreach ($tradingItems as $p) {
                dump($p->id, optional($p->latestMessage)->created_at);
            }
        }

        $reviewCount = Review::where('reviewee_id', $userId)->count();
        $averageRating = $reviewCount > 0
            ? round(Review::where('reviewee_id', $userId)->avg('rating'))
            : null;

        return view('mypage.index', [
            'page' => $page,
            'sellItems' => $sellItems,
            'buyItems' => $buyItems,
            'tradingItems' => $tradingItems,
            'averageRating' => $averageRating,
        ]);
    }
    }
    