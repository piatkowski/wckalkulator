#!/bin/sh
set -eu

find assets/js \
    -path '*/vendor' -prune -o \
    -path '*/docs' -prune -o \
    -type f -name '*.js' -print0 \
    | xargs -0 -n 1 node --check
