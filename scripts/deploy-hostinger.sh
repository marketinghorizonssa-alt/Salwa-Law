#!/bin/sh
set -eu
COMMIT="${1:?commit sha required}"
BASE="$HOME/domains/hositee.com/public_html/salwa-law"
TMP="$HOME/.salwa-deploy-$$"
ARCHIVE="$TMP/site.zip"
SRC="$TMP/src"
cleanup(){ rm -rf "$TMP"; }
trap cleanup EXIT INT TERM
mkdir -p "$TMP" "$BASE/private"
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
chmod 755 "$BASE/public" "$BASE/app" "$BASE/scripts"
chmod 750 "$BASE/private"
php -l "$BASE/public/index.php" >/dev/null
php -l "$BASE/app/config.php" >/dev/null
php -l "$BASE/app/leads.php" >/dev/null
php -l "$BASE/app/views.php" >/dev/null
printf 'SALWA_DEPLOY_OK %s\n' "$COMMIT"
