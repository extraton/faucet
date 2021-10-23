FROM radianceteam/ton-client-php:1.19.0-php8.0-fpm

RUN apt update
RUN apt install git zip nginx -y
RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2.0.11 /usr/bin/composer /usr/local/bin/composer

COPY ./deploy/nginx.mime.types /etc/nginx/mime.types
COPY ./deploy/default.nginx.conf /etc/nginx/conf.d
COPY ./ /var/www/html

RUN cd /var/www/html; \
    composer install --no-dev --no-interaction --no-cache; \
    composer dump-autoload -o;
#    composer dump-env prod; \
#    rm -rf .env deploy

RUN mkdir /var/log/app/faucet -p; chmod 777 /var/log/app -R
