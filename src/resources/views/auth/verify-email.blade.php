<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>メール認証</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}" />
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" />
            </a>
        </div>
    </header>

    <div class="container">
        <div class="alert alert-info">

            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
            <div class="mailhog-link">
                <a href="http://localhost:8025" class="btn btn-success">認証はこちらから</a>
            </div>

            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <button type="submit" class="btn-warning">認証メールを再送する</button>
            </form>
        </div>
    </div>


</body>

</html>