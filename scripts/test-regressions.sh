#!/bin/sh
set -eu

for test_file in tests/*.php; do
    php -n "$test_file"
done
