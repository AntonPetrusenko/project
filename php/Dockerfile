FROM php:7.3-fpm
RUN apt-get update -y && apt-get install -y \
        git \
        libcurl4-openssl-dev \
        libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install -j$(nproc) zip json intl opcache mysqli mbstring curl fileinfo

COPY php.ini /usr/local/etc/php/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install