#!/usr/bin/env bash
# shellcheck disable=SC2155
set -e

readonly scriptPath="$(
    cd -- "$(dirname "$0")" >/dev/null 2>&1
    pwd -P
)"
readonly rootPath="${scriptPath}/../.."
readonly toolsPath="${scriptPath}/tools"
readonly phiveExecutable="${toolsPath}/phive.phar"
readonly boxExecutable="${toolsPath}/box"

function _install_phive() {
    # Early return if PHIVE is already downloaded
    if [ -f "${phiveExecutable}" ]; then
        echo >&2 "✅ PHIVE is already downloaded."
        return 0
    fi

    # Fail if GPG is not installed
    if ! test gpg; then
        echo >&2 "🚨 Error: GPG is not installed. Exiting."
        return 1
    fi

    echo >&2 "⏳ Installing PHIVE..."
    mkdir -p "$toolsPath"

    # Download PHIVE
    wget -qO "${phiveExecutable}" https://phar.io/releases/phive.phar
    wget -qO "${phiveExecutable}.asc" https://phar.io/releases/phive.phar.asc
    curl -sS https://keys.openpgp.org/vks/v1/by-fingerprint/6AF725270AB81E04D79442549D8A98B29B2D5D79 -o /tmp/key.asc
    gpg --import /tmp/key.asc >/dev/null 2>&1
    rm /tmp/key.asc
    gpg --quiet --verify "${phiveExecutable}.asc" "${phiveExecutable}" >/dev/null 2>&1
    chmod +x "${phiveExecutable}"

    echo >&2 -e "\033[1A\033[K✅ Installed PHIVE."
}

function _install_box() {
    if [ -f "${boxExecutable}" ]; then
        echo >&2 "✅ Box is already installed."
    else
        echo >&2 "⏳ Installing box..."
        "${phiveExecutable}" install --copy --target "${toolsPath}" --trust-gpg-keys 2DF45277AEF09A2F humbug/box >/dev/null
        echo >&2 -e "\033[1A\033[K✅ Installed Box."
    fi
}

function _build_phar() {
    echo >&2 "⏳ Installing Composer dependencies..."
    composer install --quiet --no-dev --working-dir "${rootPath}"
    echo >&2 -e "\033[1A\033[K✅ Installed Composer dependencies."

    echo >&2 "⏳ Compiling PHAR..."
    "${boxExecutable}" compile --with-docker --working-dir "${rootPath}" >/dev/null
    echo >&2 -e "\033[1A\033[K✅ Compiled PHAR."
}

_install_phive
_install_box
_build_phar

# Print empty newline
echo
