FROM php:7.2-fpm-alpine

ENV PHPIZE_DEPS \
    autoconf \
    cmake \
    file \
    g++ \
    gcc \
    imagemagick-dev \
    libc-dev \
    pcre-dev \
    make \
    git \
    pkgconf \
    re2c

RUN apk add --no-cache --virtual .persistent-deps \
    # for gd extension
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    # for intl extension
    icu-dev

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        openssl-dev \
    && pecl install imagick \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-png-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo \
        pdo_mysql \
        opcache \
        zip \
    && docker-php-ext-enable imagick \
    && apk del .build-deps \
    && rm -rf /tmp/* \
    && rm -rf /var/www \
    && mkdir -p /var/www/techo

COPY config/php.ini /usr/local/etc/php/
COPY config/fpm/php-fpm.conf /usr/local/etc/
COPY config/fpm/pool.d /usr/local/etc/pool.d

WORKDIR /var/www/techo
