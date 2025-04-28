FROM php:8.3-fpm

# 安装系统依赖和PHP扩展
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# 安装Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 设置工作目录
WORKDIR /home/web/php-template

RUN chown -R www-data:www-data /home/web/php-template
RUN chown -R 755 /home/web/php-template
