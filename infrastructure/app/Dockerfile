FROM php:8.3-fpm-alpine

# Local user arguments
ARG WWWUSER
ARG WWWGROUP

# Set environment variables
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV TZ=UTC
ENV SUPERVISOR_PHP_USER="app"

# Set working directory
WORKDIR /var/www/html/

# Install dependencies
RUN apk add --no-cache tzdata \
    npm \
    bash \
    zip \
    unzip \
    curl \
    nano \
    supervisor \
    zlib-dev  \
    libjpeg-turbo-dev  \
    libpng-dev \
    librsvg-dev \
    freetype-dev \
    libxml2-dev  \
    icu-dev  \
    imagemagick-dev \
    font-noto \
    font-dejavu

# Download PHP Extension installer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install php extensions
RUN install-php-extensions mbstring \
    json \
    xml \
    exif \
    zip \
    bcmath \
    intl \
    pcntl  \
    curl \
    pdo \
    pdo_mysql \
    soap \
    openssl \
    gd \
    imagick \
    redis
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-enable redis imagick

# Installing composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --2

# Copy configuration files
COPY ["./infrastructure/app/php.ini", "/usr/local/etc/php/conf.d/php.ini"]
COPY ["./infrastructure/app/entrypoint.sh", "/usr/local/bin/entrypoint"]

# Add new user
RUN addgroup --system --gid $WWWGROUP app
RUN adduser --ingroup app --system --shell /bin/sh --uid $WWWUSER app

# Copy existing application directory permissions
COPY --chown=app:app . /var/www/html
RUN chmod 0755 /usr/local/bin/entrypoint

# Change current user
USER app

# Expose port
EXPOSE 9000

CMD ["/usr/local/bin/entrypoint"]
