FROM php:8.2-apache

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    acl \
    file \
    gettext \
    git \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    zip \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions
RUN docker-php-ext-install \
    intl \
    pdo_mysql \
    zip

# 3. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 4. Set Apache document root to public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4.1. Configure Apache for Symfony
RUN echo "<Directory /var/www/html/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    FallbackResource /index.php\n\
</Directory>" > /etc/apache2/conf-available/symfony.conf \
    && a2enconf symfony

# 5. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Set working directory
WORKDIR /var/www/html

# 7. Copy project files
COPY . .

# 7.1. Create dummy JWT keys if missing (needed for cache warmup)
RUN mkdir -p config/jwt && \
    [ -f config/jwt/private.pem ] || openssl genrsa -out config/jwt/private.pem 2048 && \
    [ -f config/jwt/public.pem ] || openssl rsa -in config/jwt/private.pem -outform PEM -pubout -out config/jwt/public.pem

# 7.2. Ensure var directory exists and is writable
RUN mkdir -p var && chown -R www-data:www-data var

# 8. Install dependencies
# We set dummy environment variables so Symfony can warm up the cache during build
ENV APP_ENV=prod
ENV APP_SECRET=fix_later_in_dokploy_env
ENV DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
ENV DEFAULT_URI="http://localhost"
ENV CORS_ALLOW_ORIGIN="^https?://.*$"
ENV JWT_PASSPHRASE=a8470b884f22a66c4717297a39fec5c3df1384d4d325fb53c580cdc988e6bbd6
ENV JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
ENV JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem

# 8.1. Build environment variables (this bakes defaults from .env into .env.local.php)
RUN composer install --no-dev --optimize-autoloader && \
    composer dump-env prod && \
    rm -f .env .env.local .env.dev .env.test

# 9. Set permissions
RUN chown -R www-data:www-data var public

# 9.1. Setup entrypoint for migrations
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 10. Expose port 80
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
