FROM composer:2.9.5@sha256:33408676b911b57400f885f83f45947dbb9501b6af40c8d79c136a8bb6800e87 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:7.0.2@sha256:782a21cafb3c7461a7b831024b99df404ec81dad7fbee62f286b40169c764a3b AS src-cli

FROM php:8.4-cli-alpine3.22@sha256:35d2128457116c6842350c3aad3ecd123755f69e75efb047acfd6275625496e4

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
