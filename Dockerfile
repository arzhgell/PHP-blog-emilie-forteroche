FROM php:8.2-apache

# Installation des dépendances et extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip mysqli intl

# Activation du module rewrite d'Apache
RUN a2enmod rewrite

# Configuration du répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers du projet
COPY . /var/www/html/

# Rendre le script d'entrée exécutable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Permissions pour Apache
RUN chown -R www-data:www-data /var/www/html

# Exposition du port 80
EXPOSE 80

# Utiliser notre script d'entrée
ENTRYPOINT ["docker-entrypoint.sh"] 