FROM php:8.2-apache

# Instala ferramentas e extensões comuns
RUN apt-get update \
  && apt-get install -y git zip unzip libzip-dev \
  && docker-php-ext-install mysqli pdo pdo_mysql \
  && a2enmod rewrite

# Composer (copiado da imagem oficial do composer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Ajuste permissões (opcional, dependendo de como seu projeto usa arquivos)
RUN chown -R www-data:www-data /var/www/html
