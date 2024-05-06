# 基于 alpine 镜像
FROM alpine:3.19

# 维护者信息
LABEL authors="ms" \
      email="qyg2297248353@gmail.com" \
      website="https://lifebus.top" \
      description="微信模板消息推送服务" \
      originalAuthor="david <367013672@qq.com>"

# 清华镜像仓库
ARG package_url=mirrors.ustc.edu.cn
RUN if [ $package_url ] ; then sed -i "s/dl-cdn.alpinelinux.org/${package_url}/g" /etc/apk/repositories ; fi

# 设置环境变量
ENV LANG="C.UTF-8" \
    TZ="Asia/Shanghai" \
    APP_DEBUG=true \
    DB_HOST=127.0.0.1 \
    DB_PORT=3306 \
    DB_NAME=test \
    DB_USER=username \
    DB_PASSWORD=password \
    DB_CHARSET=utf8 \
    SERVER_LISTEN_PORT=8788 \
    PUSH_APP_SECRET=test \
    GATEWAY_SECRET='' \
    GATEWAY_REGISTER_LISTEN_ADDRESS=127.0.0.1 \
    GATEWAY_REGISTER_ADDRESS=127.0.0.1 \
    GATEWAY_REGISTER_PORT=1236 \
    SOCKET_BIND_TO_IP=127.0.0.1 \
    GATEWAY_START_PORT=4000 \
    REDIS_HOST=127.0.0.1 \
    REDIS_PORT=6379 \
    REDIS_DB=0 \
    REDIS_AUTH=''

# 安装 PHP 8.3、PHP Redis 扩展以及数据库连接依赖
RUN set -ex && \
    apk add --no-cache \
        curl \
        git \
        php83 \
        php83-phar \
        php83-mbstring \
        php83-pcntl \
        php83-posix \
        php83-pecl-event \
        php83-pecl-redis \
        php83-pecl-igbinary \
        php83-redis \
        php83-gd \
        php83-pdo \
        php83-pdo_mysql &&\
    ln -sf /usr/bin/php83 /usr/bin/php && \
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php && \
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    echo -e "upload_max_filesize=100M\npost_max_size=108M\nmemory_limit=1024M\ndate.timezone=${TZ}\n" > /etc/php83/conf.d/99-overrides.ini && \
    echo -e "[opcache]\nopcache.enable=1\nopcache.enable_cli=1" >> /etc/php83/conf.d/99-overrides.ini && \
    rm -rf /var/cache/apk/* /tmp/*

# 设置工作目录
WORKDIR /app

# 从代码仓库拉取代码
RUN git clone https://gitee.com/qyg2297248353/iyuucn.git .

# 暴露容器端口
EXPOSE 8788
EXPOSE 3135

# 卸载 git
RUN apk del git

# 启动应用
CMD ["php", "start.php", "restart"]
