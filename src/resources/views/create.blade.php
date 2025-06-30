<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>おすすめ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/create.css') }}" />
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
    <div class="container">
        <h1 class="page-title">商品の出品</h1>



        <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group-row">
                <label for="item_image">商品画像</label>
                <div class="custom-file-upload">
                    <div class="item-image_ttl">画像を選択する</div>

                    <div class="file-input">
                        <!-- multiple 属性を削除して1枚だけ選択 -->
                        <input type="file" name="item_image" id="item_image" class="form-control" accept="image/*">
                    </div>

                    <!-- プレビュー画像 -->
                    <img id="preview" src="" alt="画像プレビュー" class="img-preview" style="display: none;">

                </div>

                @error('item_image')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- 商品の詳細 -->
            <h2 class="section-title">商品の詳細</h2>

            <!-- カテゴリ選択 -->

            <div class="form-group">
                <label for="category_id">カテゴリ</label>
                <div id="category-buttons">
                    @foreach(['ファッション', '家電', 'インテリア', 'レディース', 'メンズ', 'コスメ', '本', 'ゲーム', 'スポーツ', 'キッチン', 'ハンドメイド', 'アクセサリー', 'おもちゃ', 'ベビー・キッズ'] as $index => $category)
                    <button type="button"
                        class="category-btn {{ old('category_id') == $index + 1 ? 'selected' : '' }}"
                        data-category-id="{{ $index + 1 }}">
                        {{ $category }}
                    </button>
                    @endforeach
                </div>

                <!-- 選択したカテゴリを非表示のinputフィールドとして表示 -->
                <div id="selected-categories"></div>

                @error('category_id')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>



            <!-- 商品の状態 -->
            <div class="form-group">
                <label for="status">商品状態</label>
                <select class="select-status" id="status" name="status">
                    <option value="" disabled selected>選択してください</option>
                    <option value="good" {{ old('status') == 'good' ? 'selected' : '' }}>良好</option>
                    <option value="no_damage" {{ old('status') == 'no_damage' ? 'selected' : '' }}>目立った傷や汚れなし</option>
                    <option value="slight_damage" {{ old('status') == 'slight_damage' ? 'selected' : '' }}>やや傷や汚れあり</option>
                    <option value="bad_condition" {{ old('status') == 'bad_condition' ? 'selected' : '' }}>状態が悪い</option>
                </select>
                @error('status')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <h2 class="section-title">商品名と説明</h2>

            <!-- 商品名 -->
            <label for="item_name">商品名</label>
            <input type="text" class="form-control" id="item_name" name="item_name" value="{{ old('item_name') }}">

            @error('item_name')
            <div class="error-message">{{ $message }}</div>
            @enderror

            <!-- ブランド名 -->
            <label for="brand_name">ブランド名</label>
            <input type="text" class="form-control" id="brand_name" name="brand_name" value="{{ old('brand_name') }}">
            @error('brand_name')
            <div class="error-message">{{ $message }}</div>
            @enderror

            <!-- 商品説明 -->
            <div class="form-group">
                <label for="description">商品の説明</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                @error('description')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- 価格 -->
            <div class="input-wrapper">
                <label for="price">商品価格</label>
                <div class="input-with-symbol">
                    <span class="currency-symbol">¥</span>
                    <input type="number" class="form-control price-input" id="price" name="price" value="{{ old('price') }}">
                </div>
                @error('price')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>


            <button type="submit" class="btn btn-primary">出品する</button>
        </form>
    </div>

    <script>
        document.getElementById('item_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const preview = document.getElementById('preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };

                reader.readAsDataURL(file);
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            const categoryButtons = document.querySelectorAll('.category-btn');
            const selectedCategoriesContainer = document.getElementById('selected-categories');

            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category-id');

                    // categoryId が 0 の場合は処理しない
                    if (categoryId === "0") {
                        return; // 無視する
                    }

                    if (this.classList.contains('selected')) {
                        this.classList.remove('selected');
                        const input = selectedCategoriesContainer.querySelector(`#category-${categoryId}`);
                        if (input) {
                            selectedCategoriesContainer.removeChild(input);
                        }
                    } else {
                        this.classList.add('selected');
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'category_id[]'; // 配列として送信
                        input.id = `category-${categoryId}`;
                        input.value = categoryId;
                        selectedCategoriesContainer.appendChild(input);
                    }
                });
            });
        });

        document.getElementById('item_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById('preview');
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });
    </script>
</body>

</html>