FROM php:8.0-fpm

RUN apt-get update && apt-get install -y git  libz-dev libmemcached-dev && rm -r /var/lib/apt/lists/*
RUN pecl install memcached
RUN echo extension=memcached.so >> /usr/local/etc/php/conf.d/memcached.ini
RUN docker-php-ext-install pdo pdo_mysql 

COPY ./vendor /var/www/html/vendor/
COPY ./src/php /var/www/html/

WORKDIR /var/www/html/