# From
FROM php:8.3.4-fpm-alpine3.19
# FROM php:8.3.6-fpm-alpine3.18

# Labels
LABEL creatorName="Vagner dos Santos Cardoso"
LABEL creatorEmail="vagnercardosoweb@gmail.com"

# Set timezone
ENV TZ=${TZ:-UTC}
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install non-dev dependencies
RUN set -eux \
  && apk add --no-cache \
  git vim zip unzip bash curl tzdata icu-libs \
  c-client make ca-certificates imap gmp gettext libssh2 yaml \
  libintl libxslt libpng libwebp imap \
  linux-headers oniguruma libxslt libpq vips \
  gmp libzip libxml2 freetds

# Install dependencies
RUN set -eux \
  && apk add --no-cache --virtual .build_deps \
  libpng-dev libwebp-dev imap-dev \
  linux-headers oniguruma-dev libxslt-dev postgresql-dev vips-dev \
  libssh2-dev gmp-dev libzip-dev libxml2-dev freetds-dev yaml-dev \
  $PHPIZE_DEPS
  
# Php extensions
RUN set -eux \  
  && docker-php-ext-install \
  mysqli \
  pdo_mysql \
  pdo_pgsql \
  pgsql\
  bcmath \
  mbstring \
  xml \
  exif \
  zip \
  soap \
  intl \
  xsl \
  pcntl \
  sockets \
  sysvmsg \
  sysvsem \
  sysvshm \
  imap \
  gmp

  # additional supports
  
  # Install xdebug
  # RUN set -eux \
  # && pecl install -o -f xdebug \
  # && docker-php-ext-enable xdebug
  
  # Install YML
  RUN set -eux \
  && pecl install -o -f yaml \
  && docker-php-ext-enable yaml

  # Install redis
  RUN set -eux \
  && pecl install -o -f redis \
  && docker-php-ext-enable redis

  # Install mongodb
  # RUN set -eux \
  # && pecl install -o -f mongodb \
  # && docker-php-ext-enable mongodb 
  
  # Install apcu
  RUN set -eux \
  && pecl install -o -f apcu \
  && docker-php-ext-enable apcu
  
  # Install ssh2
  RUN set -eux \
  && pecl install -o -f ssh2 \
  && docker-php-ext-enable ssh2
  
  # Clean php extensions
  RUN set -eux \
  && rm -rf /tmp/pear \
  && docker-php-source delete
  
  # Clean up
  RUN set -eux \
  && apk del --purge .build_deps \
  && rm -rf /var/cache/apk/*

  # install opcache
  RUN set -eux \
  opcache

  # GD with jpeg support
  RUN set -eux \  
  && apk add --no-cache \  
  zlib-dev \
  libpng-dev \
  libjpeg-turbo \
  libjpeg-turbo-dev \
  libwebp-dev \ 
  freetype \ 
  freetype-dev \   
  && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
	&& docker-php-ext-install -j$(nproc) gd

# Install composer
COPY --from=composer:2.7.2 /usr/bin/composer /usr/local/bin/composer

# Copy php settings
COPY ./php.ini ${PHP_INI_DIR}/conf.d/99-php.ini

# Copy entrypoint
COPY ./entrypoint /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Workdir
ENV WORKDIR=/var/www/app
RUN mkdir -p ${WORKDIR}
WORKDIR ${WORKDIR}

# Run entrypoint
CMD ["docker-entrypoint"]
