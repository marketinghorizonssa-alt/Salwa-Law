#!/bin/sh
set -eu
COMMIT="${1:?commit sha required}"
BASE="$HOME/domains/hositee.com/public_html/salwa-law"
TMP="$HOME/.salwa-deploy-$$"
ARCHIVE="$TMP/site.zip"
SRC="$TMP/src"
RUNTIME_BACKUP="$TMP/runtime-config.php"
cleanup(){ rm -rf "$TMP"; }
trap cleanup EXIT INT TERM
mkdir -p "$TMP" "$BASE/private"

# Preserve the server-only runtime config across public directory replacement.
if [ -f "$BASE/public/runtime-config.php" ]; then
  cp "$BASE/public/runtime-config.php" "$RUNTIME_BACKUP"
fi

curl -fsSL "https://github.com/marketinghorizonssa-alt/Salwa-Law/archive/$COMMIT.zip" -o "$ARCHIVE"
unzip -q "$ARCHIVE" -d "$SRC"
ROOT=$(find "$SRC" -mindepth 1 -maxdepth 1 -type d | head -n 1)
test -n "$ROOT"
rm -rf "$BASE/app" "$BASE/public" "$BASE/scripts"
cp -R "$ROOT/app" "$BASE/app"
cp -R "$ROOT/public" "$BASE/public"
cp -R "$ROOT/scripts" "$BASE/scripts"
cp "$ROOT/README.md" "$BASE/README.md"
printf '%s\n' "$COMMIT" > "$BASE/.deployed-commit"

if [ -f "$RUNTIME_BACKUP" ]; then
  cp "$RUNTIME_BACKUP" "$BASE/public/runtime-config.php"
else
  FEED_TOKEN=$(php -r 'echo bin2hex(random_bytes(24));')
  printf '<?php return ["feed_token"=>"%s"];\n' "$FEED_TOKEN" > "$BASE/public/runtime-config.php"
  printf 'SALWA_FEED_TOKEN:%s\n' "$FEED_TOKEN"
fi
chmod 640 "$BASE/public/runtime-config.php"

chmod 755 "$BASE/public" "$BASE/app" "$BASE/scripts"
chmod 750 "$BASE/private"
php -l "$BASE/public/index.php" >/dev/null
php -l "$BASE/app/runtime.php" >/dev/null
php -l "$BASE/app/config.php" >/dev/null
php -l "$BASE/app/leads.php" >/dev/null
php -l "$BASE/app/views.php" >/dev/null
printf 'SALWA_DEPLOY_OK %s\n' "$COMMIT"
