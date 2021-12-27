FROM composer:2 AS composer
LABEL maintainer="Elias Häußler <elias@haeussler.dev>"

FROM php:8.0-alpine
COPY --from=composer /usr/bin/composer /usr/bin/composer

ADD . /app
WORKDIR /app
RUN composer install --no-dev
RUN ln -snf /app/bin/cache-warmup /usr/local/bin/cache-warmup

ENTRYPOINT ["cache-warmup"]
