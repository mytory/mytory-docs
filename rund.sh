#!/bin/sh
# Mytory Docs — PHP built-in server (daemon, background)
# Usage: ./rund.sh
# Stop:  ./stop.sh
basedir=$(dirname $0)
php -S localhost:1111 -t "$basedir/public" "$basedir/public/index.php" > "$basedir/run.log" 2> "$basedir/error.log" &
echo $! > "$basedir/pid"
echo "Mytory Docs running on http://localhost:1111 (PID $(cat $basedir/pid))"
