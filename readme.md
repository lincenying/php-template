# php-template

#### 安装 composer
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# 镜像
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
# 取消镜像
# composer config -g --unset repos.packagist

# 安装依赖
composer install
```

#### nginx 配置

```
server {
    listen 28080;

    # 项目域名
    server_name localhost;

    location / {
        autoindex on;
        # 项目目录
        root /home/web/php/php-template/;
        index index.html index.htm index.php;
        try_files $uri $uri/ /index.php?q=$uri&$args;
    }

    location ~ \.php$ {
        # 项目目录
        root /home/web/php/php-template/;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    rewrite ^/api/ajax\/([a-zA-Z_\-]+)$ /ajax.php?action=$1 last;
    rewrite ^/api/fetch\/([a-zA-Z_\-\/]+)$ /ajax.php?action=$1 last;
}
```
