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
        root /home/web/php/php-template/app/;
        index index.html index.htm index.php;
        try_files $uri $uri/ /index.php?q=$uri&$args;
    }

    location ~ \.php$ {
        # 项目目录
        root /home/web/php/php-template/app/;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    rewrite ^/api/ajax\/([a-zA-Z_\-]+)$ /ajax.php?action=$1 last;
    rewrite ^/api/fetch\/([a-zA-Z_\-\/]+)$ /ajax.php?action=$1 last;
}
```

## docker-compose

修改`docker-compose.yml`中的`mysql_db.volumes`配置, 将宿主机数据库路径映射到容器中

```yaml
volumes:
  - /Users/lincenying/web/mysqldb:/var/lib/mysql
```

修改`docker-compose.yml`中的相关配置
```yaml
# 给php用的
DB_PORT: 3306
DB_DATABASE: cyxiaowu
DB_USERNAME: user
DB_PASSWORD: password

# 给mysql用的, 上下得一一对应
MYSQL_ROOT_PASSWORD: rootpassword
MYSQL_DATABASE: cyxiaowu
MYSQL_USER: user
MYSQL_PASSWORD: password

# php应用映射到宿主机的端口
webserver:
  ports:
    - '8084:80'
```

根据情况自行修改`nginx/conf.d/php.conf`配置, 如果域名绑定, 端口号等

如果宿主机有数据库, 或者使用外部的数据库, 可以删除`docker-compose.yml`中`mysql_db`容器, 并修改`app/inc/settings.ini.php`中的数据库配置

```bash
# 生成镜像及启动容器
# 后端服务器一起启动
docker-compose build --no-cache
docker-compose up -d

# 进入mysql_db容器
docker exec -it php-mysql-db /bin/bash
# 恢复mysql数据库
mysql -uuser -p cyxiaowu < /home/mysql/mysql.sql

# 进入app容器
docker exec -it php-app-server /bin/bash
# 安装composer依赖
cd /home/web/php-template
composer install --no-dev --no-scripts --no-autoloader
composer dump-autoload --optimize

```
