ARG NODE_VERSION=14

# Build the static assets with a
# https://github.com/dunglas/symfony-docker/issues/129#issuecomment-834189019
FROM node:${NODE_VERSION}-alpine AS symfony_assets_builder

WORKDIR /srv/app

RUN mkdir public

COPY package.json yarn.lock ./

RUN yarn install

COPY assets assets/
COPY webpack.config.js ./

RUN npx encore production
#RUN yarn build


# Docker configuration for this symfony4 project, with kind of special deps (imagick)
#
# Of note
# -------
# We assume this is a fresh, unused clone you're running docker-compose from
FROM php:7.4-fpm

RUN apt-get update && \
    apt-get install -y \
    acl \
    ssh \
    git \
    libmagickwand-dev \
    libsodium-dev \
    libyaml-dev \
    libzip-dev \
    --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN pecl install apcu
RUN pecl install imagick
RUN pecl install yaml

# Use "enable" for the extensions installed via pecl
RUN docker-php-ext-enable apcu
RUN docker-php-ext-enable imagick
RUN docker-php-ext-enable yaml

# Use "install" for the others
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install sodium
RUN docker-php-ext-install zip

WORKDIR /usr/src/app

# We assume this is a fresh, unused clone you're running docker-compose from
COPY --chown=1000:1000 . /usr/src/app

# Handle the permissions of writing to cache and logs
RUN usermod -u 1000 www-data
RUN mkdir /usr/src/app/var
RUN chown -R 1000:1000 /usr/src/app/var
RUN setfacl -R -m u:www-data:rwX -m u:1000:rwX -m u:`whoami`:rwX /usr/src/app/var
RUN setfacl -dR -m u:www-data:rwX -m u:1000:rwX -m u:`whoami`:rwX /usr/src/app/var

RUN PATH=$PATH:/usr/src/app/vendor/bin:bin

# Grab composer from its own image (I guess that's what's --from is about)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-scripts --prefer-dist --no-interaction

COPY --from=symfony_assets_builder /srv/app/public/build public/build