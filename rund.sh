#!/bin/sh
basedir=$(dirname $0)
php -S localhost:1111 -t $(dirname $0) > run.log 2> error.log  &
echo $! > $basedir/pid
