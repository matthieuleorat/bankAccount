#!/bin/bash

if [ -z "${XDEBUG_REMOTE_IP}" ];then
  XDEBUG_REMOTE_HOST_IP=$(ip route | awk '/default/ { print $3}');
  sed -i "s/!XDEBUG_REMOTE_IP!/${XDEBUG_REMOTE_HOST_IP}/" /etc/php/7.4/fpm/conf.d/zz-xdebug-settings.ini;
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = '' ]; then
    # start php-fpm
    php-fpm7.4 -F
else
    # Change to user www-data
    su www-data -s /bin/bash -c "$*"
fi