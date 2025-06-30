# PHP 8.2 FPMのイメージを使用
FROM php:8.2-fpm

# 必要なパッケージをインストール
RUN apt-get update \
  && apt-get install -y default-mysql-client zlib1g-dev libzip-dev unzip \
  && docker-php-ext-install pdo_mysql zip

# PHP設定ファイルをコピー

COPY docker/php/php.ini /usr/local/etc/php/

# Composerをインストール
RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

# 作業ディレクトリの設定
WORKDIR /var/www

# Laravelアプリケーションの中身をコピー
COPY ./src /var/www