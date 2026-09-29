FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

COPY . .

RUN composer dump-autoload --optimize

EXPOSE 8016

CMD ["sh", "-c", "php database/migrate.php database/database.sqlite && php -S 0.0.0.0:8016 -t public public/index.php"]
