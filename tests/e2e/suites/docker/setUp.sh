#!/usr/bin/env bash
set -e

# Make sure Docker is installed
if ! test docker; then
    echo >&2 "🚨 Error: Docker is not installed. Exiting."
    echo
    exit 1
fi

# Make sure jq is installed
if ! test jq; then
    echo >&2 "🚨 Error: jq is not installed. Exiting."
    echo
    exit 1
fi

# Build Docker image
echo >&2 "⏳ Building Docker image..."
docker build -qt "${DOCKER_IMAGE}" "${ROOT_PATH}" >/dev/null
echo >&2 -e "\033[1A\033[K✅ Built Docker image."
echo
