#!/bin/sh
# Mytory Docs — PHP built-in server (foreground)
# Usage: ./run.sh
php -S localhost:1111 -t "$(dirname "$0")/public" "$(dirname "$0")/public/index.php"
