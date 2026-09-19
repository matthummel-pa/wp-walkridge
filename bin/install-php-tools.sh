#!/usr/bin/env bash
# Idempotent PHP 8.3 + Composer 2 + WP-CLI for Sage/Roots local and Cloud Agent VMs.
set -euo pipefail

if ! command -v php >/dev/null 2>&1; then
  if command -v apt-get >/dev/null 2>&1; then
    sudo apt-get update -qq
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
      php8.3-cli php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip \
      php8.3-sqlite3 php8.3-intl php8.3-gd php8.3-bcmath php8.3-mysql unzip curl
  else
    echo "php is required" >&2
    exit 1
  fi
fi

if ! command -v composer >/dev/null 2>&1; then
  EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
  if [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]]; then
    echo "Composer installer checksum mismatch" >&2
    rm -f composer-setup.php
    exit 1
  fi
  sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
  rm -f composer-setup.php
fi

if ! command -v wp >/dev/null 2>&1; then
  curl -fsSL -o /tmp/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  php /tmp/wp-cli.phar --info >/dev/null
  sudo mv /tmp/wp-cli.phar /usr/local/bin/wp
  sudo chmod +x /usr/local/bin/wp
fi

php -v
composer --version
command -v wp
