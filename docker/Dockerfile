FROM php:8.3-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libzip-dev \
        libicu-dev \
        libxml2-dev \
        libssl-dev \
        pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN groupmod -o -g ${GID} www-data \
    && usermod -o -u ${UID} -g www-data www-data

COPY --from=composer:2.7 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_HOME=/tmp/composer \
    PATH=/var/www/html/vendor/bin:$PATH

USER www-data

CMD ["php-fpm"]