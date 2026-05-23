FROM php:8.2-apache

# Instalar extensión PDO MySQL (necesaria para conectar a MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Activar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar todo el proyecto al directorio web de Apache
COPY . /var/www/html/

# Dar permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

# Render usa el puerto 80
EXPOSE 80
