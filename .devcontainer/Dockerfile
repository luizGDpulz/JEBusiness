# .devcontainer/Dockerfile
FROM php:8.2-apache

ARG DEBIAN_FRONTEND=noninteractive

# instalar dependências e extensões PHP comuns
RUN apt-get update && apt-get install -y \
    git \
    zip unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo_mysql mysqli zip mbstring exif opcache bcmath \
    && a2enmod rewrite headers

# Composer (instalador oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Config mínima de php.ini (erro exibido durante dev)
RUN { \
      echo 'display_errors=On'; \
      echo 'display_startup_errors=On'; \
      echo 'error_reporting=E_ALL'; \
    } > /usr/local/etc/php/conf.d/dev-php.ini

# Cria usuário vscode (mesma convenção do Codespaces)
ARG USER=vscode
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} ${USER} || true \
    && useradd -m -u ${UID} -g ${GID} -s /bin/bash ${USER} || true

WORKDIR /var/www/html

# garantir permissões corretas
RUN chown -R ${USER}:www-data /var/www/html \
    && chmod -R 775 /var/www/html

EXPOSE 80

# usar usuário vscode por padrão (Codespaces também usa)
USER ${USER}
