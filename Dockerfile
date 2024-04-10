FROM docker.productsup.com/cde/cde-php-cli-base:8.2

COPY src/ ./src
COPY config/ ./config
COPY bin/ ./bin
COPY .env composer.json composer.lock symfony.lock ./

ARG COMPOSER_AUTH=local
RUN composer install --no-dev
RUN bin/console cache:warmup --env=prod

CMD ["php", "bin/console"]