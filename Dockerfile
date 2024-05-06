FROM alpine:latest
LABEL authors="ms"
LABEL email="qyg2297248353@gmail.com"
LABEL website="https://lifebus.top"
LABEL description="微信模板消息推送服务"
LABEL originalAuthor="david <367013672@qq.com>"

# 镜像源
ARG package_url=mirrors.ustc.edu.cn
RUN if [ $package_url ] ; then sed -i "s/dl-cdn.alpinelinux.org/${package_url}/g" /etc/apk/repositories ; fi

# 安装PHP环境
RUN set -ex && \
    apk add --no-cache \
        ca-certificates \
        curl \
        bash \
        openssl \
        wget \
        zip \
        unzip \
        tzdata \
        git \
        libressl \
        tar \
        s6-overlay \
        php83 \
        php83-bz2 \
        php83-bcmath \
        php83-curl \
        php83-dom \
        php83-fileinfo \
        php83-mbstring \
        php83-openssl \
        php83-opcache \
        php83-pcntl \
        php83-pdo \
        php83-pdo_sqlite \
        php83-phar \
        php83-posix \
        php83-simplexml \
        php83-sockets \
        php83-session \
        php83-sodium \
        php83-sqlite3 \
        php83-zip \
        php83-gd \
        php83-mysqli \
        php83-pdo_mysql \
        php83-pecl-event \
        php83-redis \
        php83-xml && \
    ln -sf /usr/bin/php83 /usr/bin/php && \
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php && \
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    echo -e "upload_max_filesize=100M\npost_max_size=108M\nmemory_limit=1024M\ndate.timezone=${TZ}\n" > /etc/php83/conf.d/99-overrides.ini && \
    echo -e "[opcache]\nopcache.enable=1\nopcache.enable_cli=1" >> /etc/php83/conf.d/99-overrides.ini && \
    rm -rf /var/cache/apk/* /tmp/*

# 项目安装
RUN set -ex && \
    mkdir /app && \
    git config --global --add safe.directory /app && \

# 暴露端口
EXPOSE 8788
EXPOSE 3131
