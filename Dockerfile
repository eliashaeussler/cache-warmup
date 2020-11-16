FROM composer:2
LABEL maintainer="Elias Häußler <elias@haeussler.dev>"

ADD . /app
RUN ln -snf /app/bin/cache-warmup /usr/local/bin/cache-warmup

ENTRYPOINT ["cache-warmup"]
