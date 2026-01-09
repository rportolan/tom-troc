FROM php:8.2-apache

# Install extensions nécessaires pour MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Activer mod_rewrite pour une future réécriture d'URL (MVC propre)
RUN a2enmod rewrite

# On définit le DocumentRoot sur /var/www/html/public (dossier public)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# On remplace le DocumentRoot dans la conf Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
