FROM php:8.1.10

RUN apt-get update -y \
    && apt-get install -y libmcrypt-dev zip unzip git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo

WORKDIR /app
COPY . /app

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer install

RUN php bin/console doctrine:database:create
RUN php bin/console doctrine:migrations:migrate 20230104012755

EXPOSE 8000
CMD php -S 0.0.0.0:8000 -t public

