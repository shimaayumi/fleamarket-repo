<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>おすすめ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
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
                @csrf
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

    <!-- 🛠️ ページタイトル -->
    <div class="title-links">
        <a href="{{ route('index') }}"
            class="page page-recommended {{ !request()->has('page') ? 'active' : '' }}">
            おすすめ
        </a>

        <a href="{{ url('/?page=mylist' . (request('keyword') ? '&keyword=' . request('keyword') : '')) }}"
            class="page page-mylist {{ request()->get('page') === 'mylist' ? 'active' : '' }}">
            マイリスト
        </a>
    </div>


    {{-- 🛠️ 商品リスト表示（マイリストの場合はログイン中のみ表示） --}}
    @if(($page ?? 'all') !== 'mylist' || Auth::check())
    <div class="item-list">
        @forelse($items ?? collect() as $item)
        <div class="item">
            <a href="{{ route('item.show', ['item_id' => $item->id]) }}" class="item-link">
                <div class="item-image">
                    @if($item->images && $item->images->isNotEmpty())
                    <img src="{{ asset('storage/images/' . $item->images->first()->item_image) }}" alt="{{ $item->item_name }}">
                    
                    @else
                    <div class="no-image">商品画像</div>
                    @endif


                    @if ($item->sold_flag)
                    <div class="sold-label">SOLD</div>
                    @endif

                </div>
                <h3 class="item-name">{{ $item->item_name }}</h3>
            </a>
        </div>
        @empty
        <p></p>
        @endforelse
    </div>
    @else
    <p></p>
    @endif