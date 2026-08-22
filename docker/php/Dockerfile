FROM serversideup/php:8.5-fpm-nginx

USER root

# Install PostgreSQL PHP extensions
RUN install-php-extensions pdo_pgsql pgsql

# Set web root and index file to user_upload.php
ENV NGINX_WEB_ROOT="/var/www/html"
ENV NGINX_INDEX_FILE="user_upload.php"
ENV PHP_AUTORUN_SCHEDULE_WORKER="false"

# Copy custom Nginx header buffer and fallback configuration
COPY docker/php/nginx-custom.conf /etc/nginx/conf.d/headers.conf

# Copy backend codebase
COPY --chown=www-data:www-data . /var/www/html

USER www-data

WORKDIR /var/www/html
