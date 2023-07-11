FROM composer:2.5.8@sha256:c44511894122bc47589f8071f3e7f95b34b3c9b8bd8d8f9de93d3c340712fb48 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.1.0@sha256:c7e4865c65623aba97fd031d3f6dc6ac15478e88fd8a6092029bb13d77117f47 AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:a5f33900d8e9bf1594061971d06dfebfbcc6fa9d54e45e9a9648761ba0b6d075

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
