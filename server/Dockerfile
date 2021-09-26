FROM php:8.0-cli

RUN apt-get update && apt-get install -y git
RUN docker-php-ext-install pdo pdo_mysql

COPY . /usr/src/myapp
WORKDIR /usr/src/myapp

CMD [ "php", "server.php" ]