#!/usr/bin/env bash
# shellcheck disable=SC2155
set -e

readonly output="$(
    docker run --rm -v "${FIXTURES_PATH}:${FIXTURES_PATH}:ro" \
        -e "CACHE_WARMUP_LIMIT=1" \
        "${DOCKER_IMAGE}" \
        "${FIXTURES_PATH}/sitemap.xml" --format json 2>/dev/null
)"

readonly expected=1
readonly actual="$(echo "${output}" | jq '.cacheWarmupResult | flatten | length')"

if [ "$actual" -eq "$expected" ]; then
    echo >&2 "✅ Successful (Expected: ${expected}, actual: ${actual})"
else
    echo >&2 "🚨 Failed (Expected: ${expected}, actual: ${actual})"
    exit 1
fi
