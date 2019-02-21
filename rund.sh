#!/bin/sh
php -S localhost:1111 -t $(dirname $0) > /dev/null &
echo $! > /tmp/mytorydocs
