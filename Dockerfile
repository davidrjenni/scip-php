FROM composer:2.9.5@sha256:743aebe48ca67097c36819040633ea77e44a561eca135e4fc84c002e63a1ba07 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:7.0.3@sha256:4701d86c7561fa68cf7cfd8499e8d2a02189bf4ca8dc82fe0f9ca4cb0c98ce7e AS src-cli

FROM php:8.4-cli-alpine3.22@sha256:35d2128457116c6842350c3aad3ecd123755f69e75efb047acfd6275625496e4

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
