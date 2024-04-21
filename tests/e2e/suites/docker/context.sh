#!/usr/bin/env bash
# shellcheck disable=SC2155
set -e

export DOCKER_IMAGE="eliashaeussler/cache-warmup:test-$(git rev-parse --short HEAD)"
