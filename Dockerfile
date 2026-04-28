FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure pdo_mysql && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN useradd -m -s /bin/bash -u 1000 laravel

WORKDIR /var/www

COPY --chown=laravel:laravel . .

ENV DB_CONNECTION=mysql
ENV DB_HOST=mysql
ENV DB_PORT=3306

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

RUN composer dump-autoload --optimize --ignore-platform-reqs --no-scripts

RUN npm install && npm run build || true

RUN chown -R laravel:laravel /var/www

USER root

EXPOSE 9000

CMD ["php-fpm"]