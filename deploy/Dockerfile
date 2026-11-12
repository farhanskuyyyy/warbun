# Runtime VPS: PHP-FPM saja (nginx tetap di host, fastcgi ke container ini).
# Sama seperti deploy/Dockerfile di pitchticker — beda port (8092) & path mount.
FROM php:8.3-fpm

# extension yang dipakai host PHP
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath gd zip exif \
    && rm -rf /var/lib/apt/lists/*

# listen di loopback host:8092 — compose pakai network_mode: host,
# jadi 127.0.0.1 = loopback host dan Laravel tetap bisa akses MySQL (root@localhost).
# docker.conf juga set listen=9000 dan dimuat belakangan, jadi pakai zz- biar menang.
RUN sed -i 's|^;clear_env = no|clear_env = no|' /usr/local/etc/php-fpm.d/www.conf \
    && printf 'listen = 127.0.0.1:8092\nclear_env = no\n' > /usr/local/etc/php-fpm.d/zz-listen.conf

RUN { echo 'opcache.enable=1'; echo 'opcache.memory_consumption=128'; } \
    > /usr/local/etc/php/conf.d/zz-opcache.ini

# code di-mount dari host; path sama persis dengan nginx root
WORKDIR /var/www/warbun

EXPOSE 8092
