FROM composer:2.6.6@sha256:6d19e1002b091908fc4654725d4055125f181c1b6d7610206b0ee31b8419a5fc AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.2.1@sha256:74538d7652ed3022ad390f9c8a851b95ce2117240b0599e40903162e0f42577b AS src-cli

FROM php:8.3-cli-alpine3.17@sha256:af0809570027627bd16e89dea01fefcec427a1220dcaa494ee9d7afdfcfc2fcc

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
