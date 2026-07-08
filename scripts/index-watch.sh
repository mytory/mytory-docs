#!/bin/bash
#
# Mytory Docs — Live search index watcher
# Watches doc_root directories and updates SQLite FTS5 index on file changes.
#
# Dependencies (one of):
#   macOS:   brew install fswatch
#   Linux:   apt install inotify-tools
#
# Usage:
#   ./scripts/index-watch.sh              Foreground (Ctrl+C to stop)
#   ./scripts/index-watchd.sh             Daemon (background, PID tracked)
#   ./scripts/stop-index-watch.sh         Stop daemon

set -e

BASEDIR="$(cd "$(dirname "$0")/.." && pwd)"
INDEXER="php $BASEDIR/scripts/indexer.php"

# Extract doc_root paths from config.php
echo "[index-watch] Reading doc_roots from config.php..."

ROOTS=$(php -r '
    $_SERVER["HTTP_HOST"] = "localhost";
    $_SERVER["REQUEST_URI"] = "/";
    require "'"$BASEDIR"'/config.php";
    foreach ($doc_roots as $name => $path) {
        if (is_dir($path)) echo "$path\n";
    }
' 2>/dev/null)

if [ -z "$ROOTS" ]; then
    echo "[index-watch] ERROR: No valid doc_roots found in config.php" >&2
    exit 1
fi

echo "[index-watch] Watching doc_roots:"
echo "$ROOTS" | while read r; do echo "  $r"; done
echo ""

# macOS: fswatch
if command -v fswatch &>/dev/null; then
    echo "[index-watch] Using fswatch (macOS)"
    echo "$ROOTS" | fswatch -0 \
        --event Created \
        --event Updated \
        --event Renamed \
        --event Removed \
        --exclude '/\.git/' \
        --exclude '/vendor/' \
        --exclude '/backup/' \
        --exclude '/node_modules/' \
        --from-stdin \
    | $INDEXER watch

# Linux: inotifywait
elif command -v inotifywait &>/dev/null; then
    echo "[index-watch] Using inotifywait (Linux)"
    echo "$ROOTS" | while read r; do
        echo "  $r"
    done
    inotifywait -m -r \
        -e create -e modify -e delete -e move \
        --format '%w%f' \
        --exclude '(\.git|vendor|backup|node_modules)/.*' \
        $ROOTS \
    | $INDEXER watch

else
    echo "[index-watch] ERROR: Neither fswatch nor inotifywait found." >&2
    echo "  Install:" >&2
    echo "    macOS: brew install fswatch" >&2
    echo "    Linux: apt install inotify-tools" >&2
    exit 1
fi
