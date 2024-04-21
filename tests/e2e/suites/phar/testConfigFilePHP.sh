#!/usr/bin/env bash
# shellcheck disable=SC2155
set -e

readonly output="$(
    "${PHAR_FILE}" "${FIXTURES_PATH}/sitemap.xml" --config "${FIXTURES_PATH}/config.php" --format json 2>/dev/null
)"

readonly expected=1
readonly actual="$(echo "${output}" | jq '.cacheWarmupResult | flatten | length')"

if [ "$actual" -eq "$expected" ]; then
    echo >&2 "✅ Successful (Expected: ${expected}, actual: ${actual})"
else
    echo >&2 "🚨 Failed (Expected: ${expected}, actual: ${actual})"
    exit 1
fi
