FROM composer:2.6.4@sha256:fd0b4f28a5070d4361d5cbfe7f7807a10bf2efe9396e42009f9e63f7fa307e38 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.2.0@sha256:52924bb68cb2324cb20e7f2d91fd8fab94d0587cb8bd064066e07b63eca7d3ba AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:71f45d57cef850a8da957925b86d163130ffaee26307d47d3054d2d3f89bbbd1

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
