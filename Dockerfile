FROM composer:2.6.5@sha256:3758579a1ae643d006a80f3cb8d6c843d8f17f48bbf8bdae7bb5d605296b7e66 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.2.1@sha256:74538d7652ed3022ad390f9c8a851b95ce2117240b0599e40903162e0f42577b AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:8dad04893fc1ad3f2b76578015bb31caab9e51bc3d05b0d836b2d9a886a9387b

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
