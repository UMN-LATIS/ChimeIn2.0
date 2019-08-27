FROM php:7.2-apache-stretch

# COPY . /var/www/html
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/start

RUN apt-get update && apt-get install -y zlib1g-dev git && \
    docker-php-ext-install pdo pdo_mysql zip

COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html \
    && chmod u+x /usr/local/bin/start \
    && a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install


# RUN php artisan migrate && \
    # php artisan config:cache

CMD ["/usr/local/bin/start"]