FROM php:8.4-fpm-alpine

RUN apk update && apk add --no-cache \
    openssl \
    bash \
    mysql-client \
    autoconf \
    nginx \
    gcc \
    g++ \
    make \
    $PHPIZE_DEPS \
    linux-headers  # <--- Essencial para compilar o Xdebug

# Instala extensões do PHP
RUN docker-php-ext-install pdo pdo_mysql bcmath

# Instala e ativa o Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configuração básica do Xdebug
RUN echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www

RUN rm -rf /var/www/html && \
    ln -s public html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY ./nginx/nginx.conf /etc/nginx/http.d/

COPY . .
COPY ./start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

ENTRYPOINT [ "/start.sh" ]
