<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>商品詳細 - {{ $item->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/show.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <header>
        <div class="header">
            <div class="header__inner">
                <a class="header__logo" href="/">
                    <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" />
                </a>
            </div>

            <!-- 🛠️ 検索フォーム -->
            <form action="{{ route('index') }}" method="GET" class="search-form">
                <input type="text" name="keyword" value="{{ old('keyword', request('keyword')) }}" placeholder="なにをお探しですか？" />
                <input type="hidden" name="page" value="{{ request('page', 'all') }}" />
            </form>

            <!-- 🛠️ ヘッダーメニュー -->

            @auth
            <!-- ログイン中の表示（ログアウト） -->
            <a href="{{ route('logout') }}" class="btn"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                ログアウト
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            @endauth

            @guest
            <!-- 未ログインの表示（ログイン） -->
            <a href="{{ route('login') }}" class="btn">ログイン</a>
            @endguest
            <a href="{{ route('mypage', ['page' => 'sell']) }}" class="btn">マイページ</a>

            <a href="{{ route('sell') }}" class="btn btn-outlet">
                <span class="btn-text">出品</span>
            </a>


        </div>
    </header>

    <!-- 商品詳細 -->
    <div class="item-detail">
        <!-- 商品詳細ページ -->
        <div class="item-details">
            <div class="item-image">
                @if($item->sold_flag)
                <div class="sold-label"></div>
                @endif

                @foreach($images as $image)
                <div>
                    <p>{{ $image->item_image }}</p> <!-- 画像ファイル名を出す -->
                    <img src="{{ asset('storage/images/' . $image->item_image) }}" alt="{{ $item->item_name }}" />
                </div>
                @endforeach

                @if($images->isEmpty())
                <span>商品画像なし</span>
                @endif
            </div>

            <!-- 右側 商品情報 -->
            <div class="item-info">
                <h1 class="item-title">{{ $item->item_name }}</h1>
                <p class="brand-name">ブランド名 {{ $item->brand_name }}</p>


                <div class="price">
                    <span class="currency">¥</span>{{ number_format($item->price) }}
                    <span class="tax-included">(税込)</span>
                </div>





                <!-- いいね＆コメントセクション -->
                <div class="interaction-section">
                    <!-- いいねアイコン -->
                    <div class="like-section">
                        <span id="like-icon-{{ $item->id }}" class="like-icon" onclick="toggleLike(@json($item->id))">
                            @auth
                            {{ auth()->user()->likes()->where('item_id', $item->id)->exists() ? '★' : '☆' }}
                            @else
                            ☆
                            @endauth
                        </span>
                        <span id="like-count-{{ $item->id }}" class="like-count">{{ $item->likes->count() }}</span>
                    </div>

                    <!-- コメントアイコン -->
                    <div class="comment-section">
                        <span id="comment-icon-{{ $item->id }}" class="comment-icon">💬</span>
                        <span id="comment-count-{{ $item->id }}" class="icon-comment-count">{{ $item->comments()->count() }}</span>
                    </div>
                </div>




                <form action="{{ route('purchase.show', ['item_id' => $item->id]) }}" method="get">


                    <button type="submit" class="btn btn-primary">購入手続きへ</button>
                </form>


                <h3 class="section-title">商品説明</h3>
                <p class="description">{{ $item->description }}</p>

                <h3 class="section-title">商品の情報</h3>
                <!-- カテゴリー表示 -->
                <div class="item-container">
                    <div class="category-block">
                        <span class="category-label">カテゴリー</span>
                        <div class="category-items">
                            @foreach($categories as $category)
                            <span class="category-item">{{ $category->category_name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="item-status">商品の状態 <span>{{ $item->status }}</span></div>
                </div>






                <div class="comments-section">
                    <h3 class="comment-title">コメント <span class="comment-count"> ({{ count($item->comments) }})</span></h3>



                    <div class="seller-profile">
                        @if($item->user && $item->user->profile)
                        @if($item->user->address && $item->user->profile->profile_image)
                        <img id="preview" class="seller-image" src="{{ asset('storage/profiles/' . ($item->user->profile->profile_image ?? 'default.png')) }}" alt="画像プレビュー">
                        @else
                        <!-- 画像がない場合でも枠だけ表示 -->
                        <div class="seller-image no-image"></div>
                        @endif
                        <p class="seller-name"><strong>{{ $item->user->name }}</strong></p>
                        @else
                        <p>ユーザー情報がありません。</p> <!-- ユーザーが存在しない場合のフォールバック -->
                        @endif
                    </div>


                    @foreach($item->comments as $comment)
                    <div class="comment-item">
                        <span class="comment-user">{{ $comment->user->name }}</span>
                        <p class="comment">{{ $comment->comment }}</p>
                    </div>
                    @endforeach
                </div>
                @foreach($comments as $comment)
                <div class="comment">
                    <strong>{{ $comment->user->name }}</strong>
                    <p>{{ $comment->comment }}</p> <!-- 'content' を 'comment_text' に変更 -->
                </div>
                @endforeach




                <form action="{{ route('items.comment', $item->id) }}" method="POST">
                    @csrf

                    <h3 class="section-comment_title">商品へのコメント</h3>
                    <textarea name="comment" class="form-control">{{ old('comment') }}</textarea>
                    @if ($errors->has('comment'))
                    <div class="alert-danger">
                        {{ $errors->first('comment') }}
                    </div>
                    @endif
                    <button type="submit" class="btn btn-primary">コメントを送信する</button>
                </form>
            </div>
        </div>
        <script>
            const isLoggedIn = @json(auth()->check());
        </script>
        <script>
            function toggleLike(itemId) {
                if (!isLoggedIn) {
                    // 未ログインならログインページにリダイレクト
                    window.location.href = '/login';
                    return;
                }

                fetch(`/toggle-like/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Server Response:", data);
                        if (data.message === 'Success') {
                            const likeIcon = document.getElementById(`like-icon-${itemId}`);
                            const likeCount = document.getElementById(`like-count-${itemId}`);
                            likeIcon.innerText = data.isLiked ? '★' : '☆';
                            likeCount.innerText = data.likeCount;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }


            function updateCommentCount(itemId) {
                fetch(`/item/${itemId}/comments/count`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById(`comment-count-${itemId}`).innerText = data.commentCount;
                    })
                    .catch(error => console.error('Error:', error));
            }


            // 画像プレビュー用のJavaScript
            function previewImage(event) {
                const preview = document.getElementById('preview');
                const file = event.target.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                };

                if (file) {
                    reader.readAsDataURL(file);
                }
            }
        </script>
</body>

</html>