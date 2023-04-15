FROM composer:2.5.5@sha256:6b8dbded0cfb109dd3b06902ba4b0406d9eda689ccce5363c46f8bf25451b083 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.0.3@sha256:4157b67e4775649078c1c971c89869ad792172a04877690a736f5fa7af3db484 AS src-cli

FROM php:8.2-cli-alpine3.17

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php

WORKDIR /src
