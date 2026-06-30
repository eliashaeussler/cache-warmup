#!/usr/bin/env bash
# shellcheck disable=SC2155
set -e

readonly scriptPath="$(cd -- "$(dirname "$0")" >/dev/null 2>&1; pwd -P)"
readonly rootPath="${scriptPath}/../.."

# Restore dev dependencies so the working tree is never left in a --no-dev
# state (which strips phpunit, phpstan, php-cs-fixer, rector, ...).
echo >&2 "⏳ Restoring Composer dev dependencies..."
composer install --quiet --working-dir "${rootPath}"
echo >&2 -e "\033[1A\033[K✅ Restored Composer dev dependencies."

# Print empty newline
echo
