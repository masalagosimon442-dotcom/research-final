FROM php:8.2-apache

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mysqli curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate

# Set document root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Configure Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/serve-cgi-bin.conf
RUN echo '<Directory /var/www/html>\n\tAllowOverride All\n</Directory>' > /etc/apache2/conf-available/allowoverride.conf \
    && a2enconf allowoverride

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies if composer.json exists
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader --ignore-platform-reqs 2>/dev/null || true; fi

# Create upload directories
RUN mkdir -p /var/www/html/assets/uploads/avatars \
    /var/www/html/assets/uploads/compounds \
    /var/www/html/assets/uploads/organisms \
    /var/www/html/assets/uploads/passports

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/assets/uploads

# Configure Apache to use PORT from environment variable
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:10000/' /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

CMD ["apache2-foreground"]
