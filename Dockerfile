FROM composer:2.5.8@sha256:6226bcb18ca89f6c3131e589783a162370e3e6c532dfc9615b782bed70c6f614 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.0.3@sha256:4157b67e4775649078c1c971c89869ad792172a04877690a736f5fa7af3db484 AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:2af057cd287e0ac4421d8cd5f078846a95f2f2666b29b5ab448b42479ef09bd5

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
