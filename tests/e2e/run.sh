#!/usr/bin/env bash
# shellcheck disable=SC2155
set -e

readonly scriptPath="$(cd -- "$(dirname "$0")" >/dev/null 2>&1; pwd -P)"
readonly rootPath="$(realpath "${scriptPath}/../..")"
readonly fixturesPath="${scriptPath}/fixtures"
readonly suitesPath="${scriptPath}/suites"

readonly GREEN="\033[32m"
readonly RESET="\033[0m"

# Resolve test suites
if [ -n "$1" ]; then
    suite="${suitesPath}/${1}"

    if [ ! -d "${suite}" ]; then
        echo >&2 "🚨 Error: Suite \"${1}\" does not exist. Exiting."
        exit 1
    fi

    if [ -n "$2" ]; then
        singleTestCase="${2}"
    fi

    suites=("${1}")
else
    suites=()

    for suite in "${suitesPath}"/*; do
        suites+=("$(basename "${suite}")")
    done
fi

function _setUp() {
    if [ -f "${scriptPath}/setUp.sh" ]; then
        "${scriptPath}/setUp.sh"
    fi
}

function _run_tests() {
    local suite="$1"
    local suitePath="${suitesPath}/${suite}"
    local result=0
    local test

    # Expose common variables
    # bashsupport disable=BP2001
    export FIXTURES_PATH="${fixturesPath}"
    # bashsupport disable=BP2001
    export ROOT_PATH="${rootPath}"

    echo -e "▷ Test suite: ${GREEN}${suite}${RESET}"

    # Include test variables
    if [ -f "${suitePath}/context.sh" ]; then
        # shellcheck disable=SC1090,SC1091
        source "${suitePath}/context.sh"
    fi

    # Run suite set up
    if [ -f "${suitePath}/setUp.sh" ]; then
        if ! bash "${suitePath}/setUp.sh"; then
            return 1
        fi
    fi

    # Run test cases
    for test in "${suitePath}"/test*.sh; do
        if ! _run_test_case "${test}"; then
            result=1
        fi
    done

    # Run suite tear down
    if [ -f "${suitePath}/tearDown.sh" ]; then
        if ! bash "${suitePath}/tearDown.sh"; then
            return 1
        fi
    fi

    return "${result}"
}

function _run_test_case() {
    local test="$1"
    local testCase="$(basename "${test/.sh/}")"
    local result=0

    # Skip test case if only a specific test case is requested
    if [ -n "${singleTestCase}" ] && [ "${testCase}" != "${singleTestCase}" ]; then
        return 0
    fi

    echo -e "▶ Test case: ${GREEN}${testCase}${RESET}"
    echo
    if ! bash "${test}"; then
        result=1
    fi
    echo

    return "${result}"
}

function _tearDown() {
    local result="$1"

    if [ -f "${scriptPath}/tearDown.sh" ]; then
        "${scriptPath}/tearDown.sh"
    fi

    if [ "${result}" -eq 0 ]; then
        echo 'Result: ✅ OK'
        exit 0
    else
        echo 'Result: 🚨 Failed'
        exit 1
    fi
}

_setUp

trap '_tearDown "$result"' EXIT

result=0

for suite in "${suites[@]}"; do
    if ! _run_tests "${suite}"; then
        result=1
    fi
done
