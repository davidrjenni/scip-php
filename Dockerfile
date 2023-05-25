FROM composer:2.5.7@sha256:40fea1182aa0fe24f389e20c6604e881a775e1c6b5457c2d74cdda8f5206808d AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.0.3@sha256:4157b67e4775649078c1c971c89869ad792172a04877690a736f5fa7af3db484 AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:7f1fe24c167076f76532e0ea6d1515ee18fae25b174f5776a383b764d3896948

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
