FROM php:7.4.30-apache

WORKDIR /var/www/html

RUN apt-get update

RUN apt-get install -y libzip-dev libpng-dev zip

RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql gd zip

RUN docker-php-ext-enable pdo pdo_mysql gd zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . ./

RUN composer update

COPY ./config/docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -

RUN apt-get -y install nodejs

RUN npm install

RUN rm -rf /tmp/*
