FROM composer:2.8.2@sha256:cc12714aa3da014260179539c8bcfd64202cff69c25a5741642d1bb2e7d8fd62 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.8.2@sha256:f45c2bf682b2f6b9bf2410e7da1e9a9329219a637a65b94de417a20b0885e4dd AS src-cli

FROM php:8.3-cli-alpine3.17@sha256:af0809570027627bd16e89dea01fefcec427a1220dcaa494ee9d7afdfcfc2fcc

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
