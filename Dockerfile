FROM composer:2.9.5@sha256:743aebe48ca67097c36819040633ea77e44a561eca135e4fc84c002e63a1ba07 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:7.0.2@sha256:782a21cafb3c7461a7b831024b99df404ec81dad7fbee62f286b40169c764a3b AS src-cli

FROM php:8.5.5-cli-alpine3.22@sha256:d08798fad9e21090645913c3550b08242436f9ced979312c23f2284ed06aacb9

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
