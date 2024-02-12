FROM php:5.6-fpm-alpine

LABEL maintainer="Ghostry <ghostry.green@gmail.com>"

RUN set -ex \
    && apk --no-cache add postgresql-libs postgresql-dev \
    && docker-php-ext-install -j$(nproc) pgsql pdo_pgsql \
    && apk del postgresql-dev

RUN docker-php-ext-install -j$(nproc) bcmath calendar exif opcache pcntl shmop

RUN set -ex \
    && apk --no-cache add icu icu-dev icu-libs \
    && docker-php-ext-install -j$(nproc) intl \
    && apk del icu-dev

RUN docker-php-ext-install -j$(nproc) mysql pdo pdo_mysql \
    && apk --no-cache add mysql-client bash mariadb-connector-c

ENV TZ=Europe/Moscow

ARG PHP_IDE_CONFIG=host.docker.internal

RUN curl -sS https://getcomposer.org/installer | tee composer-setup.php \
    && php composer-setup.php && rm composer-setup.php* \
    && chmod +x composer.phar && mv composer.phar /usr/bin/composer

ARG SYMFONY_CLI_VERSION

RUN wget https://github.com/symfony/cli/releases/download/v$SYMFONY_CLI_VERSION/symfony_linux_amd64.gz \
    && gzip -d symfony_linux_amd64.gz \
    && mv symfony_linux_amd64 symfony \
    && chmod +x symfony \
    && mv symfony /usr/local/bin/

COPY ./docker/php-fpm/conf.d/symfony.ini /etc/php56/conf.d/
COPY ./docker/php-fpm/conf.d/symfony.ini /etc/php56/cli/conf.d/
COPY ./docker/php-fpm/conf.d/symfony.ini /usr/local/etc/php/conf.d/
COPY ./docker/php-fpm/php-fpm.d/symfony.pool.conf /etc/php56/php-fpm.d/
COPY ./docker/php-fpm/php-fpm.d/www.conf /etc/php56/php-fpm.d/
#COPY ./docker/php-fpm/conf.d/symfony.pool.conf /etc/php56/cli/conf.d/
#COPY ./docker/php-fpm/conf.d/symfony.pool.conf /usr/local/etc/php/conf.d/
#COPY ./docker/php-fpm/conf.d/www.conf /etc/php56/conf.d/
#COPY ./docker/php-fpm/conf.d/www.conf /etc/php56/cli/conf.d/
#COPY ./docker/php-fpm/conf.d/www.conf /usr/local/etc/php/conf.d/

ARG WORKING_DIR

RUN ln -s $WORKING_DIR/app/console /usr/bin/console

CMD ["php-fpm", "-F"]

EXPOSE 9000
