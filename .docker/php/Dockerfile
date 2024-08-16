FROM php:8.3-fpm

# Step 1: Install basic utilities
RUN apt-get update && apt-get install -y \
    wget \
    gnupg2 \
    curl \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Step 2: Install development tools
RUN apt-get update && apt-get install -y \
    build-essential \
    vim \
    git \
    procps \
    jq \
    && rm -rf /var/lib/apt/lists/*

# Step 3: Install libraries needed for PHP extensions and other utilities
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*

# Step 4: Install MySQL client
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Step 5: Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql intl gd zip

# Step 6: Install Redis via PECL and enable it
RUN pecl install redis \
    && docker-php-ext-enable redis

# Step 7: Install Composer
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Set the environment variable to allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Step 8: Install Composer
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Step 9: Node.js and Yarn installation (optional, remove if not needed)
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update && apt-get install -y nodejs \
    && npm install -g npm@latest yarn \
    && rm -rf /var/lib/apt/lists/*

# Final steps
WORKDIR /var/www
EXPOSE 5173
