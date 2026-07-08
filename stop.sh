#!/bin/bash
# Mytory Docs — Stop daemon
basedir=$(dirname $0)
if [ -f "$basedir/pid" ]; then
    kill $(cat "$basedir/pid") 2>/dev/null
    rm "$basedir/pid"
    echo "Mytory Docs stopped."
else
    echo "No PID file found."
fi
