# coachtechフリマ

## 環境構築
### Dockerビルド
- git clone https://github.com/shimaayumi/fleamarket.git
- docker-compose up -d --build

### Laravel環境構築
- docker-compose exec php bash
- composer install
- cp .env.example .env , 環境変数を適宜変更
- php artisan key:generate
- php artisan migrate
- php artisan db:seed
- php artisan storage:link

## 開発環境
  - 商品一覧画面：http://localhost/  
  - 会員登録画面: http://localhost/register  
  - phpMyAdmin：http://localhost:8080/

## 使用技術(実行環境)
- PHP 8.2.11
- Laravel 8.83.8
- MySQL 8.0.26
- nginx 1.21.1

## ER図
- [ER図](./public/images/er_fleamarket.png)

## stripeの設定
【1】Stripeアカウントの準備
Stripe公式サイト （https://dashboard.stripe.com/register）にアクセスし、アカウントを作成します。

開発モード（テストモード）に切り替え、「APIキー」を取得します。
- 公開可能キー（Publishable key）
- シークレットキー（Secret key）

【2】Laravel プロジェクトに Stripe ライブラリを導入
composer require stripe/stripe-php
【
3】環境変数（.env）に Stripe の API キーを追加
.env ファイルに以下を追記してください：

STRIPE_PUBLIC=公開可能キー
STRIPE_SECRET=シークレットキー


## Mailhogの設定について
- MAIL_FROM_ADDRESS はメール送信元のアドレスとして使われます。
- 開発環境ではMailhogを使ってメールをローカルでキャッチし、実際には送信しません。
- Mailhogを使うことで、実際のメール送信サーバーを使わずにメール内容の確認ができます。
- MailhogのWebインターフェースは http://localhost:8025 でアクセス可能です。
- メール送信に関する.envの他の設定（MAIL_MAILER, MAIL_HOST, MAIL_PORTなど）もMailhogに合わせて設定してください。

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Coachtech Flea Market"
- Docker構成でMailhogコンテナが起動していることを確認してください。


 ## ダミーデータについて

開発・テスト用のダミーデータを下記のシーダーで登録しています。  
詳細は「Laravel環境構築」セクションの `php artisan db:seed` をご参照ください。

### ダミーデータの内容

- ユーザー3名（ユーザーA、ユーザーBは商品出品あり、ユーザーCはなし）  
- 商品は合計10点（ユーザーA：5点、ユーザーB：5点）  
- 全商品はカテゴリID1に紐づいています

### 画像について

- 商品画像はS3から取得し `storage/app/public/images` に保存されます。  
- 画像公開のため `php artisan storage:link` を必ず実行してください。

