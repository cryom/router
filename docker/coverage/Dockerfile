FROM php:7.1-cli
MAINTAINER Albert Sultanov <bert.sultanov.contact@gmail.com>
RUN apt-get update \
    && apt-get install -y git \
    && rm -rf /var/lib/apt/lists/*
COPY ./conf.d /usr/local/etc/php/conf.d
WORKDIR /app
ENTRYPOINT ["./vendor/bin/test-reporter"]
