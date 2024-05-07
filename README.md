# 爱语飞飞

微信模板消息推送接口

基于 [workerman](https://www.workerman.net/) 开发的超高性能PHP框架

## 项目说明

本项目基于 [大卫 / iyuucn](https://gitee.com/ledc/iyuucn) 的项目进行二次开发，感谢大卫的开源分享。

为了方便部署，调整部分插件安装时机。构建出镜像版本，方便部署。

镜像后期维护将由 [新疆萌森软件开发工作室](https://lifebus.top/) 继续支持与维护。

## 项目安装

> 环境准备
>
> MySQL 5.7+
>
> Redis 5.0+
>
> 微信公众号

### 1Panel 第三方应用商店安装 `推荐`

### docker-compose 安装

将目录 `docker` 下文件放置安装目录下

或创建文件 `docker-compose.yml`

```yml
version: "3"

services:
  iyuucc:
    image: qyg2297248353/iyuucc:v0.0.1
    container_name: iyuucc
    restart: always
    network_mode: bridge
    ports:
      - ${SERVER_LISTEN_PORT}:8788
      - ${SERVER_WEBSOCKET_PORT}:3153
    volumes:
      - ${IYUUCC_ROOT_PATH}/app:/app
    env_file:
      - .env
```

环境变量文件 `.env`

```env
APP_DEBUG = true

IYUUCC_ROOT_PATH = /home/data

DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = iyuu
DB_USER = iyuu
DB_PASSWORD = iyuu_password
DB_CHARSET = utf8

SERVER_HOST= 127.0.0.1
SERVER_LISTEN_PORT = 8788
SERVER_WEBSOCKET_PORT = 3153

PUSH_APP_SECRET = test

GATEWAY_SECRET =
GATEWAY_REGISTER_LISTEN_ADDRESS = 127.0.0.1
GATEWAY_REGISTER_ADDRESS = 127.0.0.1
GATEWAY_REGISTER_PORT = 1236
SOCKET_BIND_TO_IP = 127.0.0.1
GATEWAY_START_PORT = 4000

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_AUTH=
REDIS_DB=0

REDIS_QUEUE_HOST=127.0.0.1
REDIS_QUEUE_PORT=6379
REDIS_QUEUE_AUTH=
REDIS_QUEUE_DB=0
```

### docker 安装

## 写在最后

感谢大家的支持，如果有任何问题，欢迎提交 [Issues](https://github.com/QYG2297248353/iyuucn/issues)。
