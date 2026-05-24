FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Activar mod_rewrite y mod_headers
RUN a2enmod rewrite headers

# Configurar Apache para permitir .htaccess y CORS de sesión
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/nutrisucre.conf \
    && a2enconf nutrisucre

# Configuración de PHP para sesiones y producción
RUN echo "session.cookie_samesite = Lax" >> /usr/local/etc/php/conf.d/sessions.ini \
    && echo "session.cookie_secure = 0" >> /usr/local/etc/php/conf.d/sessions.ini \
    && echo "session.gc_maxlifetime = 86400" >> /usr/local/etc/php/conf.d/sessions.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/prod.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/prod.ini

# Copiar todo el proyecto al directorio web de Apache
COPY . /var/www/html/

# Dar permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

# Render usa el puerto 10000 por defecto, pero Apache escucha 80
# Render se encarga del proxy — exponemos 80
EXPOSE 80

CMD ["apache2-foreground"]
