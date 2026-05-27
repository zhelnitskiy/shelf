.PHONY: setup up down build restart bash artisan composer swagger test fix lint stan check migrate-fresh seed-fresh

setup:
	sh ./setup.sh

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

restart:
	docker compose down && docker compose up -d

bash:
	docker compose exec app bash

artisan:
	docker compose exec app php artisan $(cmd)

composer:
	docker compose exec app composer $(cmd)

test:
	docker compose exec app php artisan test

lint:
	docker compose exec app composer lint

stan:
	docker compose exec app composer stan

check: lint stan test

fix:
	docker compose exec app composer format

migrate-fresh:
	docker compose exec app php artisan migrate:fresh

seed-fresh:
	docker compose exec app php artisan migrate:fresh --seed

swagger:
	docker compose exec app php artisan l5-swagger:generate
