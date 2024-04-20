#!/usr/bin/env bash
# shellcheck disable=SC2155
set -e

docker rmi "${DOCKER_IMAGE}" >/dev/null
