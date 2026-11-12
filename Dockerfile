FROM php:8.2-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Activer le module Apache rewrite
RUN a2enmod rewrite

# Configurer Apache pour écouter sur le port dynamique de Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Autoriser les .htaccess (AllowOverride All)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Configurer PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Augmenter les limites PHP pour les uploads de bijoux
RUN echo "upload_max_filesize = 20M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = 25M" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit = 256M" >> "$PHP_INI_DIR/php.ini" \
    && echo "max_execution_time = 60" >> "$PHP_INI_DIR/php.ini"

# Copier tous les fichiers du projet dans le dossier web Apache
COPY . /var/www/html/

# Donner les bons droits
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposer le port (Render utilise $PORT)
EXPOSE ${PORT}

# Démarrer Apache
CMD ["apache2-foreground"]
