FROM composer:2.6.2@sha256:a8847e8317196aac538d532866a7ea67155aff42af729743c1394e3ed894a39f AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.1.0@sha256:c7e4865c65623aba97fd031d3f6dc6ac15478e88fd8a6092029bb13d77117f47 AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:a085a2185991abd5ee2710b087154f09291a27beb00637d9f2b22151ab87b9bd

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
