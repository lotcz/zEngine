# syntax=docker/dockerfile:1

# Create a stage for installing app dependencies defined in Composer.
FROM composer:lts as deps

WORKDIR /app

# Download dependencies as a separate step to take advantage of Docker's caching.
RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=cache,target=/tmp/cache \
    composer update --no-interaction

FROM php:8.3.9-cli as final

# Copy the app dependencies from the previous install stage.
COPY --from=deps app/vendor /app/vendor

ENTRYPOINT /app/vendor/bin/phpunit --bootstrap /app/tests/autoload.php  /app/tests
