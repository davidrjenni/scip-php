FROM composer:2.9.7@sha256:dc292c5c0f95f526b051d4c341bf08e7e2b18504c74625e3203d7f123050e318 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:7.5.0@sha256:4d6792fb3b48fb6482dccd87fb52e1e99d683539a9cf86f30c71f8be185391ed AS src-cli

FROM php:8.5-cli-alpine3.22@sha256:059d73f7c8a2dec863876d9095bb5fdd37dbf21108d27b50211cd09757bb86ca

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
