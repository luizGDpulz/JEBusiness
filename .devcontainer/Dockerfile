FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive

# Instala MariaDB + cliente, dependências PHP e phpMyAdmin
RUN apt-get update && apt-get install -y --no-install-recommends \
      mariadb-server mariadb-client \
      git zip unzip libzip-dev \
      wget \
  && docker-php-ext-install pdo pdo_mysql mysqli \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/* \
  # Instala phpMyAdmin manualmente
  && mkdir -p /var/www/phpmyadmin \
  && wget -O /tmp/phpmyadmin.zip "https://files.phpmyadmin.net/phpMyAdmin/5.2.1/phpMyAdmin-5.2.1-all-languages.zip" \
  && unzip /tmp/phpmyadmin.zip -d /tmp/ \
  && cp -r /tmp/phpMyAdmin-5.2.1-all-languages/* /var/www/phpmyadmin/ \
  && rm -rf /tmp/phpmyadmin.zip /tmp/phpMyAdmin-5.2.1-all-languages

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Expor portas (informativo)
EXPOSE 80 3306

# Workdir - não força /workspaces aqui, será o mount do Codespaces
WORKDIR /var/www/html

# Não altera DocumentRoot aqui (será feito no init.sh se houver /public).

# Adiciona alias do phpMyAdmin no Apache
RUN echo "Alias /phpmyadmin /var/www/phpmyadmin\n<Directory /var/www/phpmyadmin>\n  Options Indexes FollowSymLinks\n  AllowOverride All\n  Require all granted\n</Directory>" > /etc/apache2/conf-available/phpmyadmin.conf \
  && a2enconf phpmyadmin