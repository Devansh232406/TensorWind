# Use official PHP image with Composer built in
FROM php:8.2-cli

# Install unzip and composer
RUN apt-get update && apt-get install -y unzip git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

# Copy project files into container
COPY . .

# Install PHP dependencies if you have composer.json
RUN if [ -f composer.json ]; then composer install; fi

# Expose port
EXPOSE 10000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000"]
