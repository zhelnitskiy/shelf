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

wait_for_mysql() {
    elapsed=0

    while [ "$elapsed" -le 60 ]; do
        if docker compose exec -T mysql sh -lc 'mysqladmin ping -h 127.0.0.1 -uroot -p"$MYSQL_ROOT_PASSWORD" --silent' >/dev/null 2>&1; then
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

docker compose up -d --build

wait_for_mysql

docker compose exec -T app composer install --no-interaction --prefer-dist
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan migrate:fresh --seed --force
docker compose exec -T app php artisan l5-swagger:generate

touch .setup-complete

APP_PORT=$(read_app_port)
printf 'Shelf is ready: http://localhost:%s/\n' "$APP_PORT"
