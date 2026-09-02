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

mkdir -p "$BASE/public/assets/fonts"
fetch_asset(){ curl --retry 3 --retry-delay 1 -fsSL "$1" -o "$2"; test -s "$2"; }
fetch_asset "https://fonts.gstatic.com/s/notokufiarabic/v27/CSRk4ydQnPyaDxEXLFF6LZVLKrodrOYFFlKp.woff2" "$BASE/public/assets/fonts/noto-kufi-arabic-arabic.woff2"
fetch_asset "https://fonts.gstatic.com/s/notokufiarabic/v27/CSRk4ydQnPyaDxEXLFF6LZVLKrodrJ8FFlKp.woff2" "$BASE/public/assets/fonts/noto-kufi-arabic-math.woff2"
fetch_asset "https://fonts.gstatic.com/s/notokufiarabic/v27/CSRk4ydQnPyaDxEXLFF6LZVLKrodrI0FFlKp.woff2" "$BASE/public/assets/fonts/noto-kufi-arabic-symbols.woff2"
fetch_asset "https://fonts.gstatic.com/s/notokufiarabic/v27/CSRk4ydQnPyaDxEXLFF6LZVLKrodrO0FFlKp.woff2" "$BASE/public/assets/fonts/noto-kufi-arabic-latin-ext.woff2"
fetch_asset "https://fonts.gstatic.com/s/notokufiarabic/v27/CSRk4ydQnPyaDxEXLFF6LZVLKrodrOMFFg.woff2" "$BASE/public/assets/fonts/noto-kufi-arabic-latin.woff2"

cat "$BASE/public/assets/font-local.css" "$BASE/public/assets/site.css" "$BASE/public/assets/visual-v4.css" "$BASE/public/assets/visual-v5.css" > "$BASE/public/assets/site.bundle.css"
test -s "$BASE/public/assets/site.bundle.css"

fetch_asset "https://sba.gov.sa/wp-content/uploads/2022/03/whitelogo-2.png" "$TMP/sba-logo.png"
php -r '$in=$argv[1];$out=$argv[2];$info=getimagesize($in);if(!$info)exit(2);$w=$info[0];$h=$info[1];$scale=min(232/$w,128/$h,1);$nw=max(1,(int)round($w*$scale));$nh=max(1,(int)round($h*$scale));$src=imagecreatefrompng($in);if(!$src)exit(3);$dst=imagecreatetruecolor($nw,$nh);imagealphablending($dst,false);imagesavealpha($dst,true);$clear=imagecolorallocatealpha($dst,0,0,0,127);imagefilledrectangle($dst,0,0,$nw,$nh,$clear);imagecopyresampled($dst,$src,0,0,0,0,$nw,$nh,$w,$h);if(!imagewebp($dst,$out,82))exit(4);imagedestroy($src);imagedestroy($dst);' "$TMP/sba-logo.png" "$BASE/public/assets/authority-sba.webp"
test -s "$BASE/public/assets/authority-sba.webp"
fetch_asset "https://www.hrsd.gov.sa/themes/custom/hrsd_saud/logo.svg" "$BASE/public/assets/authority-hrsd.svg"

chmod 755 "$BASE/public" "$BASE/app" "$BASE/scripts"
chmod 750 "$BASE/private"
php -l "$BASE/public/index.php" >/dev/null
php -l "$BASE/app/runtime.php" >/dev/null
php -l "$BASE/app/config.php" >/dev/null
php -l "$BASE/app/leads.php" >/dev/null
php -l "$BASE/app/views.php" >/dev/null
php -l "$BASE/app/enhancements.php" >/dev/null
printf 'SALWA_DEPLOY_OK %s\n' "$COMMIT"
