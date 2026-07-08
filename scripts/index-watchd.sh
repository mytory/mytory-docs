#!/bin/bash
#
# Mytory Docs — Index watcher daemon (background)
# Same as index-watch.sh but runs in background with PID tracking.
# Usage: ./scripts/index-watchd.sh
# Stop:  ./scripts/stop-index-watch.sh

set -e
BASEDIR="$(cd "$(dirname "$0")/.." && pwd)"
PIDFILE="$BASEDIR/scripts/.index-watch.pid"

if [ -f "$PIDFILE" ] && kill -0 $(cat "$PIDFILE") 2>/dev/null; then
    echo "[index-watchd] Already running (PID $(cat $PIDFILE))"
    exit 0
fi

nohup "$BASEDIR/scripts/index-watch.sh" > "$BASEDIR/scripts/.index-watch.log" 2>&1 &
PID=$!
echo $PID > "$PIDFILE"
echo "[index-watchd] Started (PID $PID)"
echo "  Log:  $BASEDIR/scripts/.index-watch.log"
echo "  Stop: $BASEDIR/scripts/stop-index-watch.sh"
