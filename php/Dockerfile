FROM php:7.3-fpm
RUN apt-get update -y && apt-get install -y \
        git \
        libicu-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libsqlite3-dev \
        libedit-dev \
        libbz2-dev \
        libcurl4-openssl-dev \
        libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pdo_sqlite readline zip gd json intl opcache pdo_mysql mysqli mbstring bz2 calendar curl fileinfo exif gettext iconv hash

RUN docker-php-ext-enable opcache

RUN pecl install xdebug-2.7.2 \
    && docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer