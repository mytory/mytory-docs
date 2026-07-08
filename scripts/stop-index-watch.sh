#!/bin/bash
#
# Mytory Docs — Stop index watcher daemon
# Usage: ./scripts/stop-index-watch.sh

BASEDIR="$(cd "$(dirname "$0")/.." && pwd)"
PIDFILE="$BASEDIR/scripts/.index-watch.pid"

if [ ! -f "$PIDFILE" ]; then
    echo "[stop-index-watch] No PID file found. Not running."
    exit 0
fi

PID=$(cat "$PIDFILE")
if kill -0 $PID 2>/dev/null; then
    kill $PID
    rm "$PIDFILE"
    echo "[stop-index-watch] Stopped (PID $PID)"
else
    rm "$PIDFILE"
    echo "[stop-index-watch] Process not running. Removed stale PID file."
fi
