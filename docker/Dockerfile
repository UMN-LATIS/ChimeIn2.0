FROM composer:2 as vendor

COPY database/ database/

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist


FROM node:10.16.3-stretch as frontend
WORKDIR /app
RUN mkdir -p /app/public
COPY package-lock.json package.json webpack.mix.js /app/
RUN npm install
COPY resources /app/resources

COPY .babelrc /app
COPY artisan /app

RUN npm run prod

FROM php:7.2-apache-stretch

RUN apt-get update && apt-get install -y zlib1g-dev git libjpeg-dev libpng-dev libfreetype6-dev imagemagick libmagickwand-dev  && \
    pecl install imagick  && \
    docker-php-ext-install pdo pdo_mysql zip  pcntl && \
    docker-php-ext-enable imagick

COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/js/ /var/www/html/public/js/
COPY --from=frontend /app/public/css/ /var/www/html/public/css/
COPY --from=frontend /app/public/mix-manifest.json /var/www/html/public/mix-manifest.json
# COPY --from=frontend /app/node_modules/ /var/www/html/node_modules

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/start

COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html  && \
    ln -s /var/www/html/storage/app/public /var/www/html/public/storage && \
    chmod -R 777 /var/www/html/storage && \
    chmod u+x /usr/local/bin/start && \
    a2enmod rewrite


CMD ["/usr/local/bin/start"]