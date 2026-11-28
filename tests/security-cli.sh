#!/usr/bin/env bash
set -euo pipefail
tmp="$(mktemp -d)"
server=""
cleanup() {
    if [[ -n "$server" ]]; then kill "$server" 2>/dev/null || true; fi
    rm -rf "$tmp"
}
trap cleanup EXIT
mkdir -p "$tmp/wp-content/plugins/events-made-easy"
cp cli_mail.php "$tmp/wp-content/plugins/events-made-easy/"
printf '<?php echo "BOOTSTRAPPED";' > "$tmp/wp-load.php"
php -S 127.0.0.1:18793 -t "$tmp" > "$tmp/server.log" 2>&1 &
server=$!
url="http://127.0.0.1:18793/wp-content/plugins/events-made-easy/cli_mail.php"
status="$(curl --retry 10 --retry-connrefused --retry-delay 1 -sS -o "$tmp/body" -w '%{http_code}' "$url")"
[[ "$status" == 403 ]]
[[ ! -s "$tmp/body" ]]
