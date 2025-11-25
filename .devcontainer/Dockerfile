FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive

# Instala MySQL server + cliente e dependências PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
      mysql-server mysql-client \
      git zip unzip libzip-dev \
  && docker-php-ext-install pdo pdo_mysql mysqli \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Expor portas (informativo)
EXPOSE 80 3306

# Workdir - não força /workspaces aqui, será o mount do Codespaces
WORKDIR /var/www/html

# Não altera DocumentRoot aqui (será feito no init.sh se houver /public).