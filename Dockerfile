FROM php:8.3.14-apache

WORKDIR /var/www/html

RUN apt-get update

RUN apt-get install -y libzip-dev libpng-dev zip git

RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql gd zip

RUN docker-php-ext-enable pdo pdo_mysql gd zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . ./

RUN composer update

COPY ./config/docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -

RUN echo "upload_max_filesize=512M\npost_max_size=512M\nmemory_limit=1024M" \
    > /usr/local/etc/php/conf.d/custom.ini

RUN rm -rf /tmp/*
