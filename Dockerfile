FROM php:7.4-fpm

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

# 复制依赖文件并安装
COPY app/composer.json app/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

# 复制项目文件
COPY . .

# 生成自动加载
RUN composer dump-autoload --optimize
