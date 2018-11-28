# Настройка Xdebug через docker-compose.

Чтобы настроить себе Xdebug нужно:

Создать себе файл `docker-compose.override.yml` (see: https://docs.docker.com/compose/extends/#example-use-case)
с содержимым:

```yml
version: '2'

services:
    app:
        extra_hosts:
            - "dockerhost:10.0.75.1"
        environment:
            PHP_IDE_CONFIG: "serverName=localhost"
        volumes:
            - ./.docker/php/55-xdebug.ini:/usr/local/etc/php/conf.d/55-xdebug.ini
```


Файл `55-xdebug.ini` необходимо создать с содержимым, либо указать другой уже имеющийся

```ini
; INI
zend_extension=xdebug.so

xdebug.remote_host=dockerhost
xdebug.remote_port=9001
xdebug.remote_connect_back=0
```

Чтобы промонтировать новый файл необходимо перезапустить сервис `app` 
(если ранее он был собраный пересборка пройдет из кеша)

```
docker-compose stop app
docker-compose build app
docker-compose up -d app
```

> Note: ip 
> может отличаться
>
> На Mac OS нужно настроить кастомную петлю
>
> https://forums.docker.com/t/ip-address-for-xdebug/10460
>
> `sudo ifconfig lo0 alias 10.254.254.254`
> `xdebug.remote_host=10.254.254.254`
>
