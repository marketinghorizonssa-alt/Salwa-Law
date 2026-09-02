#!/bin/sh
set -eu
ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
php -l "$ROOT/public/index.php"
php -l "$ROOT/app/config.php"
php -l "$ROOT/app/helpers.php"
php -l "$ROOT/app/leads.php"
php -l "$ROOT/app/views.php"
! grep -R "enkaf.sa\|0559556606\|559556606\|GTM-" "$ROOT/app" "$ROOT/public" "$ROOT/README.md"
grep -R "966569132168" "$ROOT/app" >/dev/null
grep -R "salwalaw.hositee.com" "$ROOT/app" "$ROOT/README.md" >/dev/null
printf 'VALIDATION_OK\n'
