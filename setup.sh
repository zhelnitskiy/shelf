#!/usr/bin/env sh

set -eu

PROJECT_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
cd "$PROJECT_ROOT"

usage() {
    cat <<'EOF'
Usage:
  ./setup.sh [--project <name>] [--port <port>] [--non-interactive]

Examples:
  ./setup.sh
  ./setup.sh --project shelf-1 --port 8001
  ./setup.sh --non-interactive

Notes:
  - Provided --project/--port are persisted into .env
  - If running interactively and values are missing, you will be prompted
EOF
}

require_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        printf 'Required dependency not found: %s\n' "$1" >&2
        exit 1
    fi
}

require_docker_compose_v2() {
    if ! docker compose version >/dev/null 2>&1; then
        printf 'Docker Compose v2 is required.\n' >&2
        exit 1
    fi
}

resolve_setup_marker() {
    printf '.setup-complete'
}

upsert_env_value() {
    key=$1
    value=$2
    tmp_file=$(mktemp)

    if grep -q "^${key}=" .env; then
        sed "s|^${key}=.*|${key}=${value}|" .env >"$tmp_file"
    else
        cat .env >"$tmp_file"
        printf '\n%s=%s\n' "$key" "$value" >>"$tmp_file"
    fi

    mv "$tmp_file" .env
}

sync_app_port_env() {
    if [ -z "${APP_PORT:-}" ]; then
        return
    fi

    upsert_env_value APP_PORT "$APP_PORT"
    upsert_env_value APP_URL "http://localhost:${APP_PORT}"
}

sync_compose_project_env() {
    if [ -z "${COMPOSE_PROJECT_NAME:-}" ]; then
        return
    fi

    upsert_env_value COMPOSE_PROJECT_NAME "$COMPOSE_PROJECT_NAME"
}

wait_for_mysql() {
    elapsed=0

    while [ "$elapsed" -lt 60 ]; do
        if docker compose exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqladmin ping -h 127.0.0.1 -uroot --silent' >/dev/null 2>&1; then
            return 0
        fi

        sleep 2
        elapsed=$((elapsed + 2))
    done

    printf 'MySQL did not become available within 60 seconds.\n' >&2
    exit 1
}

read_app_port() {
    port=$(sed -n 's/^APP_PORT=//p' .env | tail -n 1 | tr -d '\r' | sed "s/^['\"]//; s/['\"]$//")

    if [ -z "${port:-}" ]; then
        port=8000
    fi

    printf '%s' "$port"
}

require_command docker
require_docker_compose_v2

non_interactive=0
while [ "$#" -gt 0 ]; do
    case "$1" in
        --project)
            shift
            COMPOSE_PROJECT_NAME=${1:-}
            ;;
        --port)
            shift
            APP_PORT=${1:-}
            ;;
        --non-interactive)
            non_interactive=1
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            printf 'Unknown argument: %s\n\n' "$1" >&2
            usage >&2
            exit 2
            ;;
    esac
    shift
done

SETUP_MARKER=$(resolve_setup_marker)

if [ -f "$SETUP_MARKER" ]; then
    printf 'Project is already initialized.\n'
    printf 'Use make up or make restart instead.\n'
    exit 0
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ "$non_interactive" -eq 0 ] && [ -t 0 ]; then
    if [ -z "${COMPOSE_PROJECT_NAME:-}" ]; then
        printf 'Compose project name ("shelf"): '
        read -r maybe_project || true
        if [ -n "${maybe_project:-}" ]; then
            COMPOSE_PROJECT_NAME=$maybe_project
        fi
    fi

    if [ -z "${APP_PORT:-}" ]; then
        current_port=$(read_app_port)
        printf 'App port (%s): ' "$current_port"
        read -r maybe_port || true
        if [ -n "${maybe_port:-}" ]; then
            APP_PORT=$maybe_port
        fi
    fi
fi

sync_compose_project_env
sync_app_port_env

docker compose up -d --build

docker compose exec -T app sh -c 'mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs storage/api-docs && chown -R www-data:www-data bootstrap/cache storage/framework storage/logs storage/api-docs && chmod -R ug+rwX bootstrap/cache storage/framework storage/logs storage/api-docs'

wait_for_mysql

docker compose exec -T app composer install --no-interaction --prefer-dist
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan migrate:fresh --seed --force
docker compose exec -T app php artisan l5-swagger:generate

touch "$SETUP_MARKER"

final_port=${APP_PORT:-$(read_app_port)}
printf 'Shelf is ready: http://localhost:%s/\n' "$final_port"
