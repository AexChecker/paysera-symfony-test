#!/usr/bin/env bash

set +exo pipefail

export XDEBUG_MODE=off

cd /app || exit 1
composer install --ansi -o --no-interaction --no-progress
