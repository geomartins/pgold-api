FROM php:8.3-apache

LABEL author="Martins Abiodun <abiodun.martins@ab-mfbnigeria.com>" \
      version="1.0" \
      description="Pgold API with Laravel, Apache, and background queue worker"

# Install base dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libldap2-dev \
    libcurl4-openssl-dev \
    gnupg2 \
    apt-transport-https \
    cifs-utils \
    curl \
    openssl \
    zip \
    nano \
    git \
    iputils-ping \
    lsb-release \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libpq-dev \
    supervisor \
    unixodbc-dev \
    libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install mbstring zip ldap pdo pdo_pgsql gd pcntl intl fileinfo

# Remove conflicting ODBC libs, add Microsoft repo, install ODBC drivers + SQLSRV extensions
RUN apt-get remove -y libodbc2 libodbcinst2 unixodbc-common \
 && mkdir -p /etc/apt/keyrings \
 && curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/keyrings/microsoft.gpg \
 && echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
    > /etc/apt/sources.list.d/mssql-release.list \
 && apt-get update \
 && ACCEPT_EULA=Y DEBIAN_FRONTEND=noninteractive apt-get install -y \
    msodbcsql18 \
    mssql-tools18 \
    unixodbc-dev \
    gcc \
    make \
    autoconf \
    libc-dev \
    pkg-config \
 && pecl install sqlsrv pdo_sqlsrv \
 && docker-php-ext-enable sqlsrv pdo_sqlsrv \
 && apt-get remove -y gcc make autoconf libc-dev pkg-config \
 && apt-get autoremove -y \
 && rm -rf /var/lib/apt/lists/*


# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


  # Set PHP memory limit & curl CA cert
RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/memory.ini && \
    echo "curl.cainfo=/etc/ssl/certs/cacert.pem" >> /usr/local/etc/php/conf.d/curl.ini && \
    curl -o /etc/ssl/certs/cacert.pem https://curl.se/ca/cacert.pem


# Install Infisical CLI
RUN curl -1sLf 'https://artifacts-cli.infisical.com/setup.deb.sh' | bash \
 && apt-get update && apt-get install -y infisical

# Copy Laravel source
COPY --chown=www-data:www-data . /var/www/html

WORKDIR /var/www/html

# Git safe dir
RUN git config --global --add safe.directory /var/www/html

# Remove default .env (Infisical will inject it)
RUN rm -f .env

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Enable Apache modules
RUN a2enmod rewrite ssl headers

# ===== Apache Hardening =====
# Hide Apache version/signature
RUN printf "ServerTokens Prod\nServerSignature Off\n" > /etc/apache2/conf-available/security.conf \
 && a2enconf security

# Remove X-Powered-By header
RUN echo "Header unset X-Powered-By" > /etc/apache2/conf-available/remove-poweredby.conf \
 && a2enconf remove-poweredby

# Disable directory listings
RUN echo "<Directory /var/www/html>\n    Options -Indexes\n</Directory>" \
    > /etc/apache2/conf-available/disable-indexes.conf \
 && a2enconf disable-indexes

# Add strict security headers
RUN echo '<IfModule mod_headers.c>\n\
    Header always set X-Frame-Options "SAMEORIGIN"\n\
    Header always set X-Content-Type-Options "nosniff"\n\
    Header always set Referrer-Policy "strict-origin-when-cross-origin"\n\
    Header always set X-XSS-Protection "1; mode=block"\n\
    Header always set Content-Security-Policy "default-src '\''self'\''; object-src '\''none'\''; frame-ancestors '\''self'\''"\n\
</IfModule>' > /etc/apache2/conf-available/security-headers.conf \
 && a2enconf security-headers

# Block robots.txt access completely
RUN echo '<Location "/robots.txt">\n    Require all denied\n</Location>' \
    > /etc/apache2/conf-available/block-robots.conf \
 && a2enconf block-robots

# Block sitemap.xml access completely
RUN echo '<Location "/sitemap.xml">\n    Require all denied\n</Location>' \
    > /etc/apache2/conf-available/block-sitemap.conf \
 && a2enconf block-sitemap

# ===== PHP Hardening =====
RUN echo 'expose_php = Off' > /usr/local/etc/php/conf.d/security.ini

# Replace Apache vhost config
ARG ENVIRONMENT=dev
RUN echo "-------------------- ${ENVIRONMENT}" && rm -f /etc/apache2/sites-available/000-default.conf
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Generate SSL certs if missing
RUN if [ ! -f /etc/ssl/certs/server.crt ] || [ ! -f /etc/ssl/certs/server.key ]; then \
    mkdir -p /etc/ssl/certs && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/certs/server.key \
    -out /etc/ssl/certs/server.crt \
    -subj "/C=NG/ST=Lagos/L=Ikeja/O=ABMFB/OU=IT Department/CN=localhost" && \
    cp /etc/ssl/certs/server.crt /etc/ssl/certs/server.ca-bundle; \
fi

# Copy Supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
