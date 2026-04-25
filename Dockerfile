FROM composer:2.9.5@sha256:743aebe48ca67097c36819040633ea77e44a561eca135e4fc84c002e63a1ba07 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:7.2.0@sha256:ebaf97590ddf525cd3f5c1e00396d1a2fa5de31f8bdf18b4d58cfebf041440f5 AS src-cli

FROM php:8.5-cli-alpine3.22@sha256:059d73f7c8a2dec863876d9095bb5fdd37dbf21108d27b50211cd09757bb86ca

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
