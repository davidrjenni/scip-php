FROM composer:2.5.5@sha256:d718bf3b9e308a8616a12924c5e74ef565a44f83264191bb0e37aafee5254134 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.0.3@sha256:4157b67e4775649078c1c971c89869ad792172a04877690a736f5fa7af3db484 AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:58b95a3e4f49b5594b28b682733771497acaafbe7e6bc0f60575af4ea47b4df8

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
