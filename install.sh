#!/usr/bin/env bash

echo "1. install php-fpm pool config";
cp -f self_config/config-server-fpm.conf /etc/php/fpm/pool.d/

echo "2. restart php-fpm to add pool";
service php5-fpm stop
service php5-fpm start

echo "3. installing config-server.local.conf to nginx conf dir";
mkdir -p /var/log/php-fpm
cp -f self_config/config-server.local.conf /etc/nginx/sites-available/
rm /etc/nginx/sites-enabled/config-server.local.conf
ln -s /etc/nginx/sites-available/config-server.local.conf /etc/nginx/sites-enabled/config-server.local.conf

echo "4. reload nginx";
service nginx reload;

echo "5. install hosts";
grep -v "deploy.local" /etc/hosts > hosts.tmp
echo "127.0.0.1 deploy.local" >> hosts.tmp
mv hosts.tmp /etc/hosts
grep "deploy.local" /etc/hosts

echo "6. Test nginx connection. You must see the 'PONG' string"
curl "http://deploy.local/ping.txt"
echo ""

echo "7. Test php connection. You must see the 'PHP_OK' string"
curl "http://deploy.local/ping"
echo ""