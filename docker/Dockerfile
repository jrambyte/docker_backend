FROM php:8.3-apache

# Abilita modulo rewrite di Apache
RUN a2enmod rewrite

# Installa estensioni PHP per PDO (MySQL e PostgreSQL)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copia php.ini
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# DocumentRoot
WORKDIR /var/www/html

# Configura Apache per servire da /public
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf