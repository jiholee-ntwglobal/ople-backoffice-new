FROM php:7.0-fpm

# Install Memcached
RUN apt-get update && \
    apt-get install -y zlib1g-dev libmemcached-dev memcached

# INSTALL MEMCACHED DOCKER-PHP-EXT
RUN curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/v3.1.5.tar.gz" \
    && mkdir -p memcached \
    && tar -C memcached -zxvf /tmp/memcached.tar.gz --strip 1 \
    && ( \
    cd memcached \
    && phpize \
    && ./configure \
    && make -j$(nproc) \
    && make install \
    ) \
    && rm -r memcached \
    && rm /tmp/memcached.tar.gz \
    && docker-php-ext-enable memcached

# INSTALL mysqli DOCKER-PHP-EXT
RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY ["/start.sh", "/root/start.sh"]
WORKDIR /root
CMD ["./start.sh"]