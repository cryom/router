FROM php:7.1-cli
MAINTAINER Albert Sultanov <bert.sultanov.contact@gmail.com>
RUN apt-get update && rm -rf /var/lib/apt/lists/* \
    && yes | pecl install xdebug  && docker-php-ext-enable xdebug
COPY ./conf.d /usr/local/etc/php/conf.d
WORKDIR /app
ENTRYPOINT ["./vendor/bin/phpunit"]

