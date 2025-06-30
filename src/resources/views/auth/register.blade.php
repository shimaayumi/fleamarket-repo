<!DOCTYPE html>
<html lang="ja">


<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>fleamarket</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}" />
</head>

<body>
  <header class="header">
    <div class="header__inner">
      <a class="header__logo" href="/">
        <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" />
      </a>
    </div>
  </header>

  <div class="register-form">
    <h2 class="register-form__heading content__heading">会員登録</h2>
    <div class="register-form__inner">

      <form action="{{ route('register') }}" method="post">
        @csrf
        <div class="register-form__group">
          <label class="register-form__label" for="name">ユーザー名</label>
          <input class="register-form__input" type="text" name="name" id="name" value="{{ old('name') }}">
          <p class="register-form__error-message">
            @error('name')
            <span>{{ $message }}</span>
            @enderror
          </p>
        </div>
        <div class="register-form__group">
          <label class="register-form__label" for="email">メールアドレス</label>
          <input class="register-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
          <p class="register-form__error-message">
            @error('email')
            <span>{{ $message }}</span>
            @enderror
          </p>
        </div>
        <div class="register-form__group">
          <label class="register-form__label" for="password">パスワード</label>
          <input class="register-form__input" type="password" name="password" id="password">
          <p class="register-form__error-message">
            @error('password')
            <span>{{ $message }}</span>
            @enderror
          </p>
        </div>
        <div class="register-form__group">
          <label class="register-form__label" for="password_confirmation">確認用パスワード</label>
          <input class="register-form__input" type="password" name="password_confirmation" id="password_confirmation">
          <p class="register-form__error-message">
            @error('password_confirmation')
            <span>{{ $message }}</span>
            @enderror
          </p>
        </div>

        <input class="register-form__btn btn" type="submit" value="登録する">
      </form>
      <div class="register-form__login-link">
        <a class="login__link" href="{{ route('login') }}">ログインはこちら</a>
      </div>
    </div>
  </div>
</body>

</html>