#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

SELF_PATH="${BASH_SOURCE[0]}"
AUTH_FILE=''
DB_NAME_FILE=''
trap 'rm -f -- "$SELF_PATH"; [[ -z "$AUTH_FILE" ]] || rm -f -- "$AUTH_FILE"; [[ -z "$DB_NAME_FILE" ]] || rm -f -- "$DB_NAME_FILE"' EXIT

WEB_ROOT="${1:-}"
[[ "$WEB_ROOT" =~ ^/www/wwwroot/[A-Za-z0-9][A-Za-z0-9._-]*$ ]] || { printf 'Unsafe web root.\n' >&2; exit 2; }
[[ -d "$WEB_ROOT" && -f "$WEB_ROOT/wp-config.php" ]] || { printf 'WordPress root not found.\n' >&2; exit 3; }

BACKUP_PARENT='/www/backup/site/thuc-pham-thuy-trang'
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
DEST="$BACKUP_PARENT/$STAMP"
AUTH_FILE="/tmp/tt-db-auth-$STAMP.cnf"
DB_NAME_FILE="/tmp/tt-db-name-$STAMP"

required_kb="$(( $(du -sk "$WEB_ROOT" | awk '{print $1}') * 2 + 524288 ))"
available_kb="$(df -Pk "$WEB_ROOT" | awk 'NR==2 {print $4}')"
(( available_kb > required_kb )) || { printf 'Insufficient disk space for backup.\n' >&2; exit 4; }

install -d -m 700 "$DEST"

php -r '
require $argv[1] . "/wp-config.php";
$escape = static function ($value) { return str_replace(array("\\", "\n", "\r"), array("\\\\", "\\n", "\\r"), (string) $value); };
$host = (string) DB_HOST;
$port = "";
if (preg_match("/^(.*):([0-9]+)$/", $host, $m)) { $host = $m[1]; $port = $m[2]; }
$lines = array("[client]", "host=" . $escape($host), "user=" . $escape(DB_USER), "password=" . $escape(DB_PASSWORD));
if ($port !== "") { $lines[] = "port=" . $port; }
file_put_contents($argv[2], implode("\n", $lines) . "\n", LOCK_EX);
file_put_contents($argv[3], (string) DB_NAME, LOCK_EX);
chmod($argv[2], 0600); chmod($argv[3], 0600);
' "$WEB_ROOT" "$AUTH_FILE" "$DB_NAME_FILE"

mysqldump --defaults-extra-file="$AUTH_FILE" --single-transaction --quick --skip-lock-tables --default-character-set=utf8mb4 "$(cat "$DB_NAME_FILE")" | gzip -9 > "$DEST/database.sql.gz"
tar -czf "$DEST/uploads.tar.gz" -C "$WEB_ROOT/wp-content" uploads
site_name="$(basename "$WEB_ROOT")"
tar -czf "$DEST/site-source.tar.gz" -C "$(dirname "$WEB_ROOT")" \
  --exclude="$site_name/wp-content/cache" \
  --exclude="$site_name/wp-content/upgrade" \
  --exclude="$site_name/wp-content/backups" \
  "$site_name"

{
  printf 'backup_utc=%s\n' "$STAMP"
  printf 'hostname='; hostname
  printf 'kernel='; uname -srmo
  printf 'php='; php -r 'echo PHP_VERSION;' 2>/dev/null
  printf '\nmysql='; mysql --version | head -n 1
  printf 'wordpress='; php -r 'include $argv[1] . "/wp-includes/version.php"; echo $wp_version;' "$WEB_ROOT" 2>/dev/null
  printf '\nweb_owner='; stat -c '%U:%G:%a' "$WEB_ROOT"
  printf 'web_bytes='; du -sb "$WEB_ROOT" | awk '{print $1}'
} > "$DEST/environment.txt"

(
  cd "$DEST"
  sha256sum database.sql.gz uploads.tar.gz site-source.tar.gz environment.txt > SHA256SUMS
)
chmod 600 "$DEST"/*
chmod 700 "$DEST"
printf '%s\n' "$DEST"
