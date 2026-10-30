#!/bin/sh
set -eu

root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
output=${1:-"${TMPDIR:-/tmp}/wc-kalkulator.zip"}
work=$(mktemp -d "${TMPDIR:-/tmp}/wck-package.XXXXXX")
package="$work/wc-kalkulator"
trap 'rm -rf "$work"' EXIT HUP INT TERM
mkdir -p "$package"

rsync -a \
    --exclude='/.git/' \
    --exclude='/docs/' \
    --exclude='/tests/' \
    --exclude='/scripts/' \
    --exclude='/.github/' \
    --exclude='/node_modules/' \
    --exclude='/vendor/' \
    --exclude='/AGENTS.md' \
    --exclude='/REVIEW.md' \
    --exclude='/ROZWOJ.md' \
    --exclude='/.gitignore' \
    --exclude='/package.json' \
    --exclude='/package-lock.json' \
    --exclude='/.jshintrc' \
    "$root/" "$package/"

composer install --working-dir="$package" --no-dev --no-interaction --prefer-dist

test -f "$package/wc-kalkulator.php"
test -f "$package/vendor/autoload.php"
test -f "$package/views/woocommerce/product.php"
test -f "$package/assets/js/wckalkulator.min.js"
test ! -e "$package/docs"
test ! -e "$package/tests"
test ! -e "$package/scripts"

mkdir -p "$(dirname -- "$output")"
rm -f "$output"
(cd "$work" && zip -qr "$output" wc-kalkulator)
unzip -t "$output" >/dev/null
echo "Release package created and checked: $output"
