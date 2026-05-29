FROM composer:2.10.0@sha256:1b73755de4f19775ba6087fd5313664493e06fab72b6fc27dc2044e87bb7c4c3 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:7.2.1@sha256:9bec9a65851632a3ebbe7b3e94623c3f58969e345257ea44fdfe4f9a6ca1c429 AS src-cli

FROM php:8.5-cli-alpine3.22@sha256:059d73f7c8a2dec863876d9095bb5fdd37dbf21108d27b50211cd09757bb86ca

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
