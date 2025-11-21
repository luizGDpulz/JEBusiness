FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Atualiza pacotes
RUN apt-get update && apt-get upgrade -y

# Instala Apache, PHP, MySQL e libs necessárias
RUN apt-get install -y \
    apache2 \
    mysql-server \
    php \
    php-mysql \
    php-zip \
    php-xml \
    php-mbstring \
    php-curl \
    php-gd \
    php-cli \
    php-common \
    php-intl \
    libapache2-mod-php \
    git \
    unzip \
    curl

# Habilita módulos Apache
RUN a2enmod rewrite && a2enmod headers

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# DocumentRoot aponta pro seu workspace
WORKDIR /workspaces

# Deixa tudo acessível via Apache
RUN rm -rf /var/www/html && ln -s /workspaces /var/www/html

EXPOSE 80
