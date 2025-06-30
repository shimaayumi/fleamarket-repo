<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>fleamarket</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}" />
    <script src="https://js.stripe.com/v3/"></script>

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



    <div class="container">

        <!-- 左側の商品情報・配送先情報 -->
        <div class="left-column">
            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="item">

                        @if($item->sold_flag)
                        <div class="sold-label"></div>
                        @endif
                        @if(isset($item))

                        <img src="{{ asset('storage/images/' . $item->images->first()->item_image) }}" alt="{{ $item->name }}">
                        @else
                        <div class="no-image">商品画像がありません</div>
                        @endif

                        <div class="item-info">
                            <div class="item-name">
                                {{ $item->item_name ?? '商品名がありません' }}
                            </div>
                            <div class="item-price">
                                <strong><span class="currency">¥</span></strong>
                                {{ isset($item) ? number_format($item->price)  : '価格情報がありません' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- 支払い方法選択 -->
            <div class="form-group">
                <label class="payment_method">支払い方法</label>
                <select name="payment_method" id="payment_method" class="form-control custom-select" onchange="displaySelectedPaymentMethod()">
                    <option value="" disabled selected>選択してください</option>
                    <option value="convenience_store">コンビニ支払い</option>
                    <option value="credit_card">カード支払い</option>


                </select>

                <div id="payment_method_error" class="error-message"></div>
            </div>



            <!-- 配送先情報の表示 -->

            <div class="form-address">
                <div class="address-method">
                    <label class="address-method_ttl">配送先</label>


                    <!-- 住所変更ボタン -->
                    <div class="address-method__button">
                        @if(isset($item))

                        <a href="{{ route('address.edit', ['item_id' => $item->id]) }}">変更する</a>
                        @endif


                        </a>
                    </div>
                </div>


                @php
                $tempAddress = session('temporary_address');
                @endphp

                <p class="address-postal-code"><strong>〒</strong>
                    {{ $tempAddress['postal_code'] ?? $purchase->shipping_postal_code ?? $user->address->postal_code ?? '未設定' }}
                </p>

                <div class="address-wrapper">
                    <p class="address-detail">
                        {{ $tempAddress['address'] ?? $purchase->shipping_address ?? $user->address->address ?? '未設定' }}
                    </p>
                    <p class="address-building">
                        {{ $tempAddress['building'] ?? $purchase->shipping_building ?? $user->address->building ?? '未設定' }}
                    </p>
                </div>
                <div id="address_error" class="error-message"></div>
            </div>

        </div>

        <!-- 右側の価格と購入ボタン -->
        <div class="right-column">
            @if (isset($item))
            <p class="payment-amount"><strong>商品代金 </strong><span class="price">¥{{ number_format($item->price) }}</span></p>
            @endif

            <!-- 支払い方法の表示 -->

            <div class="payment-method-wrapper">
                <label class="payment-method-label">支払い方法</label>
                <div id="payment_method_display" class="payment-method"></div>
            </div>



            <!-- 購入確認フォーム -->
            <button id="checkout-button" class="checkout-button" data-item-id="{{ $item->id }}">購入する</button>

            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif






            <script src="https://js.stripe.com/v3/"></script>
            <script>
                document.addEventListener('DOMContentLoaded', async () => {
                    const urlParams = new URLSearchParams(window.location.search);
                    const paymentIntentClientSecret = urlParams.get('payment_intent_client_secret');
                    const itemId = '{{ $item->id }}'; // ここはコントローラで渡しておく必要があります

                    if (!paymentIntentClientSecret) {
                        return;
                    }

                    try {
                        const {
                            paymentIntent
                        } = await stripe.retrievePaymentIntent(paymentIntentClientSecret);

                        if (paymentIntent.status === 'succeeded') {
                            // Laravel に購入確定処理を送る
                            const response = await fetch(`/purchase/confirm/${itemId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                },
                                body: JSON.stringify({
                                    payment_intent_id: paymentIntent.id,
                                }),
                            });

                            const data = await response.json();
                            if (response.ok) {
                                alert('購入が確定されました！');
                                window.location.href = '/mypage'; // または任意のリダイレクト先
                            } else {
                                alert('確定処理に失敗しました: ' + data.error);
                            }
                        } else {
                            alert('支払いがまだ完了していません（ステータス: ' + paymentIntent.status + '）');
                        }
                    } catch (error) {
                        console.error('支払い確認エラー:', error);
                        alert('購入の確認中にエラーが発生しました。');
                    }
                });


                // 支払い方法表示用関数
                function displaySelectedPaymentMethod() {
                    const select = document.getElementById('payment_method');
                    const selectedValue = select.value;
                    let displayText = '';

                    if (selectedValue === 'convenience_store') {
                        displayText = 'コンビニ支払い';
                    } else if (selectedValue === 'credit_card') {
                        displayText = 'カード支払い';
                    }

                    const displayElement = document.getElementById('payment_method_display');
                    if (displayElement) {
                        displayElement.textContent = displayText;
                    }

                    console.log('選択された支払い方法:', displayText);
                }

                // Stripe初期化
                const stripe = Stripe("{{ env('STRIPE_PUBLIC') }}");

                document.querySelector('.checkout-button').addEventListener('click', function(event) {
                    const paymentMethod = document.getElementById('payment_method').value;
                    const paymentErrorBox = document.getElementById('payment_method_error');
                    const addressErrorBox = document.getElementById('address_error');

                    paymentErrorBox.textContent = '';
                    addressErrorBox.textContent = '';

                    let hasError = false;

                    const address = {
                        postal_code: '{{ $user->address->postal_code ?? "" }}',
                        address: '{{ $user->address->address ?? "" }}',
                        building: '{{ $user->address->building ?? "" }}',
                    };

                    // エラーチェック
                    if (!paymentMethod) {
                        paymentErrorBox.textContent = '支払い方法を選択してください';
                        hasError = true;
                    }

                    if (!address.postal_code || !address.address || !address.building) {
                        addressErrorBox.textContent = '住所情報が不足しています';
                        hasError = true;
                    }

                    if (hasError) {
                        event.preventDefault();
                        return;
                    }

                    const address_id = '{{ $user->address->id ?? "" }}'; // ユーザーの住所IDが存在する場合、それを取得

                    const dataToSend = {
                        payment_method: paymentMethod,
                        address: address,
                        address_id: address_id // 住所IDを追加
                    };

                    // データ確認
                    console.log('送信データ:', dataToSend);
                    const itemId = '{{ $item->id }}';

                    fetch(`/purchase/${itemId}/checkout`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify(dataToSend)
                        })
                        .then(async response => {
                            const contentType = response.headers.get('content-type');

                            if (!response.ok) {
                                if (contentType && contentType.includes('application/json')) {
                                    const err = await response.json();
                                    throw err;
                                } else {
                                    const text = await response.text();
                                    console.error('HTMLエラー内容:', text);
                                    throw new Error('サーバーエラーが発生しました（HTML形式）');
                                }
                            }

                            return response.json();
                        })
                        .then(data => {
                            if (data.url) {
                                window.location.href = data.url;
                            } else if (data.payment_method === 'convenience_store' && data.payment_intent_client_secret) {
                                stripe.confirmKonbiniPayment(data.payment_intent_client_secret, {
                                    payment_method: {
                                        billing_details: {
                                            name: '{{ $user->name }}',
                                            email: '{{ $user->email }}',
                                        },
                                    },
                                    return_url: '{{ route("purchase.success") }}'
                                }).then(function(result) {
                                    if (result.error) {
                                        alert('支払いエラー: ' + result.error.message);
                                    }
                                });
                            } else {
                                alert('不明な応答形式です。');
                            }
                        })
                        .catch(error => {
                            console.error('エラー詳細:', error);

                            if (error.errors) {
                                if (error.errors.payment_method) {
                                    paymentErrorBox.textContent = error.errors.payment_method.join(', ');
                                }
                                if (error.errors.address) {
                                    addressErrorBox.textContent = error.errors.address.join(', ');
                                }
                            } else if (error.error) {
                                // Laravelから返された { error: "xxx" } を拾う
                                alert('エラーが発生しました: ' + error.error);
                            } else {
                                alert('エラーが発生しました: ' + (error.message || '不明なエラー'));
                            }
                        });
                });
            </script>

</body>


</html>