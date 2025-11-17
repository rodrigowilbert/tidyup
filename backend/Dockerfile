FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    vim \
    nano \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    libjpeg-dev \
    libfreetype6-dev \
    --no-install-recommends && \
    rm -rf /var/lib/apt/lists/*

RUN pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install exif \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install opcache \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY php-fpm.conf /etc/php/8.2/fpm/pool.d/www.conf

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]

