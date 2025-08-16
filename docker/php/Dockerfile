FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    msmtp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Configure msmtp for MailHog
RUN echo "defaults" > /etc/msmtprc
RUN echo "logfile /var/log/msmtp.log" >> /etc/msmtprc
RUN echo "" >> /etc/msmtprc
RUN echo "account default" >> /etc/msmtprc
RUN echo "host mailhog" >> /etc/msmtprc
RUN echo "port 1025" >> /etc/msmtprc
RUN echo "from noreply@storeall.io" >> /etc/msmtprc
RUN echo "tls off" >> /etc/msmtprc
RUN echo "auth off" >> /etc/msmtprc

# Configure PHP to use msmtp
RUN echo "sendmail_path = '/usr/bin/msmtp -t'" >> /usr/local/etc/php/conf.d/mail.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Change current user to www
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
