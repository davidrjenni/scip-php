FROM composer:2.6.5@sha256:403855481b9b080ee79c29b301b8d1817b7ad183d477dd2c1de243831a9256d3 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.2.0@sha256:52924bb68cb2324cb20e7f2d91fd8fab94d0587cb8bd064066e07b63eca7d3ba AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:8dad04893fc1ad3f2b76578015bb31caab9e51bc3d05b0d836b2d9a886a9387b

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
