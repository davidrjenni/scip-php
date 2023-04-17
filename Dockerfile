FROM composer:2.5.5@sha256:2cf189b2623d0e84bb648512464ae68397fa153d0c28a7fa53ec686915e6f434 AS builder

COPY composer.json /app/
COPY composer.lock /app/
RUN composer install --no-dev --no-progress --no-interaction

FROM sourcegraph/src-cli:5.0.3@sha256:4157b67e4775649078c1c971c89869ad792172a04877690a736f5fa7af3db484 AS src-cli

FROM php:8.2-cli-alpine3.17@sha256:5026c54310b19d10f3fc3fc4d24e21e928cc1b2847e9202f52e73a97d2c8aebd

RUN echo 'memory_limit=2G' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini;

COPY --from=builder /app/vendor /app/vendor
COPY bin /app/bin
COPY src /app/src

COPY --from=src-cli /usr/bin/src /usr/bin/src
RUN ln -s /app/bin/scip-php /usr/bin/scip-php

WORKDIR /src
