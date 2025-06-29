# Utiliser une image officielle PHP
FROM php:8.0-apache

# Installer des extensions PHP nécessaires
RUN apt-get update && apt-get install -y libzip-dev && docker-php-ext-install zip

# Activer Apache mod_rewrite
RUN a2enmod rewrite

# Copier l'application dans le conteneur
COPY . /var/www/html/

# Exposer le port 80
EXPOSE 80