<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>fleamarket</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}" />
</head>

<body>
  <header class="header">
    <div class="header__inner">
      <a class="header__logo" href="/">
        <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" />
      </a>


    </div>
  </header>

  <div class="login-form">
    <h2 class="login-form__heading content__heading">ログイン</h2>
    <div class="login-form__inner">
      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="login-form__group">
          <label class="login-form__label" for="email">メールアドレス</label>
          <input class="login-form__input" type="email" name="email" id="email">
          <p class="register-form__error-message">
            @error('email')
            <span>{{ $message }}</span>
            @enderror
          </p>
        </div>
        <div class="login-form__group">
          <label class="login-form__label" for="password">パスワード</label>
          <input class="login-form__input" type="password" name="password" id="password">
          <p class="register-form__error-message">
            @error('password')
            <span>{{ $message }}</span>
            @enderror
          </p>
        </div>
        <input class="login-form__btn btn" type="submit" value="ログインする">

        <div class="login-form__register-link">
          <a class="register__link" href="{{ route('register') }}">会員登録はこちら</a>
        </div>
      </form>




    </div>
  </div>
</body>

</html>