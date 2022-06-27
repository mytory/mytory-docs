#!/bin/sh
php -S localhost:1111 -t $(dirname $0) > run.log 2> error.log  &
echo $! > /tmp/mytorydocs
