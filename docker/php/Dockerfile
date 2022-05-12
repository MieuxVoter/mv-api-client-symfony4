# Careful with node version, node-sass is picky and won't work with > 15
ARG NODE_VERSION=14

########################################################################################################################
# Build the static assets with a smidgeon of javascript
# https://github.com/dunglas/symfony-docker/issues/129#issuecomment-834189019
FROM node:${NODE_VERSION}-alpine AS symfony_assets_builder

WORKDIR /srv/app

RUN mkdir public

COPY package.json yarn.lock ./

RUN yarn install

COPY assets assets/
COPY webpack.config.js ./

# Creates files in public/build/, we'll copy them later into the main image
RUN npx encore production
#RUN yarn build

########################################################################################################################
# Docker configuration for this symfony4 project, with kind of special deps (imagick)
#
# Of note
# -------
# We assume this is a fresh, unused clone you're running docker-compose from
# We also assume you created and configured .env.local or .env.prod.local
FROM php:7.4-fpm

RUN apt-get update && \
    apt-get install -y \
    # Composer prefers using system zip tools than php zip
    unzip \
    # Our ugly way of dealing with permissions in ./var
    acl \
    # We need git and ssh for composer because we use git repos as some sources
    ssh \
    git \
    # PHP exts
    libmagickwand-dev \
    libsodium-dev \
    libyaml-dev \
    libzip-dev \
    # Some relevant flags
    --no-install-recommends && \
    # Image cleanup
    rm -rf /var/lib/apt/lists/*

RUN pecl install apcu
RUN pecl install imagick
RUN pecl install yaml

# Use "enable" for the extensions installed via pecl
RUN docker-php-ext-enable \
    apcu \
    imagick \
    yaml \
    ;

# Use "install" for the others
RUN docker-php-ext-install \
    pdo_mysql \
    sodium \
    zip \
    ;

WORKDIR /usr/src/app

# We assume this is a fresh, unused clone you're running docker-compose from
COPY --chown=1000:1000 . .

#RUN rm -R ./public/build
COPY --from=symfony_assets_builder /srv/app/public/build ./public/build

# Handle the permissions of writing to cache and logs
# There's a better way out there than using acl ; but this works.
RUN usermod -u 1000 www-data
RUN mkdir var
RUN chown -R 1000:1000 ./var
RUN setfacl  -R -m u:www-data:rwX -m u:1000:rwX -m u:`whoami`:rwX ./var
RUN setfacl -dR -m u:www-data:rwX -m u:1000:rwX -m u:`whoami`:rwX ./var

# This probably is not strictly mandatory
RUN PATH=$PATH:/usr/src/app/vendor/bin:/usr/src/app/bin

# Grab composer 2 from its own image ; should be safe to bump to 3
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Now it would be nice if we could cache these deps BEFORE the COPY
# so we don't re-download everything every time we build.
# We need the composer.json file to do that, and it's provided by the COPY.
# Conundrum?
RUN composer install --no-scripts --prefer-dist --no-interaction

# After composer install (which requires writing cache in odd places)
USER www-data

# Clean & Warm up the cache (on both envs, unless we can use an ARG here as well?)
#RUN rm -Rf /usr/src/app/var/cache
RUN bin/console cache:clear --env=dev
RUN bin/console cache:clear --env=prod
RUN bin/console cache:warmup --env=dev
RUN bin/console cache:warmup --env=prod

# Finally go back to root right before the entrypoint
USER root
