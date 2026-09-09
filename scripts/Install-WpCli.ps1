[CmdletBinding()]
param(
    [string] $ConfigPath = (Join-Path $PSScriptRoot '..\config\local.ps1')
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\Project.Common.ps1')
$config = Import-ProjectConfig -Path $ConfigPath

$command = @'
set -Eeuo pipefail
if command -v wp >/dev/null 2>&1; then
  wp --info
  exit 0
fi
WORK_DIR="$(mktemp -d /tmp/tt-wp-cli.XXXXXXXX)"
export GNUPGHOME="$WORK_DIR/gnupg"
install -d -m 700 "$GNUPGHOME"
trap 'rm -rf -- "$WORK_DIR"' EXIT
curl --fail --silent --show-error --location --proto '=https' --tlsv1.2 \
  --output "$WORK_DIR/wp-cli.phar" \
  https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
curl --fail --silent --show-error --location --proto '=https' --tlsv1.2 \
  --output "$WORK_DIR/wp-cli.phar.asc" \
  https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar.asc
curl --fail --silent --show-error --location --proto '=https' --tlsv1.2 \
  --output "$WORK_DIR/wp-cli.pgp" \
  https://raw.githubusercontent.com/wp-cli/builds/gh-pages/wp-cli.pgp
gpg --batch --import "$WORK_DIR/wp-cli.pgp" >/dev/null 2>&1
gpg --batch --verify "$WORK_DIR/wp-cli.phar.asc" "$WORK_DIR/wp-cli.phar"
php "$WORK_DIR/wp-cli.phar" --info >/dev/null
install -m 0755 -o root -g root "$WORK_DIR/wp-cli.phar" /usr/local/bin/wp
wp --info
'@

Invoke-ProjectSsh -Config $config -Command $command
