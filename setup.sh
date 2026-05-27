#!/usr/bin/env sh

set -eu

PROJECT_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
cd "$PROJECT_ROOT"

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

wait_for_mysql() {
    elapsed=0

    while [ "$elapsed" -le 60 ]; do
        if docker compose exec -T mysql sh -lc 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqladmin ping -h 127.0.0.1 -uroot --silent' >/dev/null 2>&1; then
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
require_command make
require_docker_compose_v2

if [ -f .setup-complete ]; then
    printf 'Project is already initialized.\n'
    printf 'Use make up or make restart instead.\n'
    exit 0
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

sync_app_port_env

docker compose up -d --build

docker compose exec -T app sh -lc 'mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs storage/api-docs && chmod -R 775 bootstrap/cache storage/framework storage/logs storage/api-docs'

wait_for_mysql

docker compose exec -T app composer install --no-interaction --prefer-dist
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan migrate:fresh --seed --force
docker compose exec -T app php artisan l5-swagger:generate

touch .setup-complete

APP_PORT=$(read_app_port)
printf 'Shelf is ready: http://localhost:%s/\n' "$APP_PORT"
