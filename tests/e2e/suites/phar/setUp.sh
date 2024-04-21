#!/usr/bin/env bash
set -e

# Make sure jq is installed
if ! test jq; then
    echo >&2 "🚨 Error: jq is not installed. Exiting."
    echo
    exit 1
fi
