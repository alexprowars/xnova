FROM php:8.4-fpm-alpine

WORKDIR /home/www/app

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN echo $TZ > /etc/timezone

RUN apk add --no-cache libpng-dev zip unzip curl sqlite bash coreutils \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl gd exif

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www

COPY docker/php/php.ini /local/etc/php/conf.d/99-web.ini

USER www

EXPOSE 9000
CMD ["php-fpm"]