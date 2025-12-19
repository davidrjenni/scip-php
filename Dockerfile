FROM composer:2.9.2@sha256:8209324ed15c43af0c5cc900cbb9fd01e94b84a94c4bb0969aa95d31d2d7e43d AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:6.11.0@sha256:35b3ad54942e1ac372071cbba1ef80b2318ebb8522224ce6244c47d86f946964 AS src-cli

FROM php:8.3-cli-alpine3.17@sha256:af0809570027627bd16e89dea01fefcec427a1220dcaa494ee9d7afdfcfc2fcc

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php
RUN apk add --no-cache git

WORKDIR /src
