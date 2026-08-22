FROM serversideup/php:8.5-fpm-nginx

USER root

# Install PostgreSQL PHP extensions
RUN install-php-extensions pdo_pgsql pgsql

# Set web root to public directory per PDS skeleton convention
ENV NGINX_WEB_ROOT="/var/www/html/public"
ENV PHP_AUTORUN_SCHEDULE_WORKER="false"

# Copy custom Nginx header buffer configuration
COPY docker/php/nginx-custom.conf /etc/nginx/conf.d/headers.conf

# Copy backend codebase
COPY --chown=www-data:www-data . /var/www/html

USER www-data

WORKDIR /var/www/html
