#!/bin/sh
set -eu

find . \
    -path './.git' -prune -o \
    -path './vendor' -prune -o \
    -path '*/vendor' -prune -o \
    -path './docs' -prune -o \
    -path '*/docs' -prune -o \
    -path './node_modules' -prune -o \
    -type f -name '*.php' -print0 \
    | xargs -0 -n 1 php -n -l
