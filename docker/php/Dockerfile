# Basic Symfony4 setup

FROM php:7.4-fpm

RUN apt-get update && \
    apt-get install -y \
#    acl \
    ssh \
    git \
    libyaml-dev \
    libzip-dev \
    libmagickwand-dev \
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
RUN docker-php-ext-install zip

WORKDIR /usr/src/app

COPY --chown=1000:1000 . /usr/src/app

RUN mkdir /usr/src/app/var
RUN chown 1000:1000 /usr/src/app/var

#RUN setfacl -R -m u:www-data:rwX -m u:`whoami`:rwX /usr/src/app/var
#RUN setfacl -dR -m u:www-data:rwX -m u:`whoami`:rwX /usr/src/app/var

#RUN setfacl -R -m u:www-data:rwX -m u:`whoami`:rwX /usr/src/app/cache /usr/src/app/logs
#RUN setfacl -dR -m u:www-data:rwX -m u:`whoami`:rwX /usr/src/app/cache /usr/src/app/logs

RUN PATH=$PATH:/usr/src/app/vendor/bin:bin

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-scripts --prefer-dist --no-interaction

