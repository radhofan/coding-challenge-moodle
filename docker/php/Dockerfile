FROM serversideup/php:8.5-fpm-nginx

USER root

# Install PostgreSQL PHP extensions
RUN install-php-extensions pdo_pgsql pgsql

# Set web root to project root directory
ENV NGINX_WEB_ROOT="/var/www/html"
ENV PHP_AUTORUN_SCHEDULE_WORKER="false"

# Copy custom Nginx configuration to route directly to user_upload.php without index.php
COPY docker/php/nginx-custom.conf /etc/nginx/conf.d/default.conf

# Copy backend codebase
COPY --chown=www-data:www-data . /var/www/html

USER www-data

WORKDIR /var/www/html
