FROM composer:2.8.10@sha256:a91ae6a2f823903231e7f1aa4617ecb3c27658c280efaa47bb5d2819b8e7dba7 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:6.5.0@sha256:cc3bd284fd0b60f345922cd162901e31ede3832b873d02c94cd660ba105e81a6 AS src-cli

FROM php:8.3-cli-alpine3.17@sha256:af0809570027627bd16e89dea01fefcec427a1220dcaa494ee9d7afdfcfc2fcc

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
