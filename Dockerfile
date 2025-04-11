FROM php:8.2-fpm-alpine
WORKDIR /var/www
# Instalar dependencias necesarias
RUN apk add --no-cache \
    mysql-client libzip libpng libjpeg-turbo libwebp freetype icu \
    icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev \
    pcre-dev $PHPIZE_DEPS

# Habilitar y configurar extensiones necesarias
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd pdo_mysql intl bcmath opcache exif zip && \
    pecl install redis && docker-php-ext-enable redis

# Configuración de PHP para producción
RUN echo "memory_limit=2048M" >> /usr/local/etc/php/conf.d/memory_limit.ini && \
    echo "upload_max_filesize=5000M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size=8000M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/time_limit.ini && \
    echo "error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT" >> /usr/local/etc/php/conf.d/error_reporting.ini && \
    echo "display_errors=Off" >> /usr/local/etc/php/conf.d/display_errors.ini && \
    echo "log_errors=On" >> /usr/local/etc/php/conf.d/log_errors.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código de la app y asegurar los permisos
COPY --chown=www-data:www-data . /var/www
RUN chown -R www-data:www-data /var/www
WORKDIR /var/www
# Iniciar PHP-FPM
USER www-data
CMD ["php-fpm"]
