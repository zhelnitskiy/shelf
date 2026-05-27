# Shelf

REST API application for a book library.

## Stack

- PHP 8.5
- Laravel 13
- Docker / Docker Compose
- MySQL

## Requirements

- Git
- Docker
- Docker Compose v2
- GNU Make (macOS / Linux / WSL)

## Setup

Use the automatic setup below. If you prefer running the setup manually, see [Manual Setup](#manual-setup).

### macOS / Linux / WSL

```bash
git clone https://github.com/zhelnitskiy/shelf.git

cd shelf

make setup
```

### Windows PowerShell

```powershell
git clone https://github.com/zhelnitskiy/shelf.git

cd shelf

.\setup.ps1
```

`setup` is intended for one-time initialization and creates `.setup-complete` after success.

To run setup again:

```bash
rm .setup-complete
```

PowerShell:

```powershell
Remove-Item .setup-complete
```

## Commands

Common commands:

| Command              | Description                            |
| -------------------- | -------------------------------------- |
| `make up`            | Start containers                       |
| `make down`          | Stop containers                        |
| `make restart`       | Restart containers                     |
| `make check`         | Run lint, PHPStan and tests            |
| `make fix`           | Format code                            |
| `make migrate-fresh` | Recreate database schema               |
| `make seed-fresh`    | Recreate database schema and seed data |
| `make swagger`       | Generate Swagger/OpenAPI documentation |

Other available commands can be found in the `Makefile`.

## Requirement Checklist

### Acceptance Criteria

- [x] Application uses PHP 8.5
- [x] Application uses Composer for dependency installation
- [x] Application supports GET / POST / PATCH / DELETE API actions
- [x] Application has PHPUnit feature tests
- [x] Application has README instructions for setup and run
- [x] Final result is posted on [GitHub](https://github.com/zhelnitskiy/shelf) with clear commit/comment history

### Plus Points

- [x] Application has a Swagger UI / OpenAPI generation flow
- [x] Application uses Laravel
- [x] Application has an automatic setup process with fixtures and migrations
- [x] Application runs in Docker with Docker Compose v2

### Book Model Coverage

- [x] Title
- [x] Publisher
- [x] Author
- [x] Genre
- [x] Book publication date
- [x] Amount of words in the book
- [x] Book price

### Notes

- `publisher`, `author`, and `genre` are implemented as separate related entities instead of plain string columns on `books`.
- Price is stored as a decimal amount plus a 3-letter `currency` field. Current examples and seed data use `USD`, but the API is not restricted to USD only.

## Manual Setup

If automatic setup fails, run the initialization steps manually.

### macOS / Linux / WSL

```bash
cp .env.example .env

chmod 644 .env

docker compose up -d --build

docker compose exec -T app sh -c 'mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs storage/api-docs && chown -R www-data:www-data bootstrap/cache storage/framework storage/logs storage/api-docs && chmod -R ug+rwX bootstrap/cache storage/framework storage/logs storage/api-docs'

docker compose exec -T app composer install --no-interaction --prefer-dist

docker compose exec -T app php artisan key:generate --force

docker compose exec -T app php artisan migrate:fresh --seed --force

docker compose exec -T app php artisan l5-swagger:generate

touch .setup-complete
```

### Windows PowerShell

```powershell
Copy-Item .env.example .env

docker compose up -d --build

docker compose exec -T app sh -c 'mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs storage/api-docs && chown -R www-data:www-data bootstrap/cache storage/framework storage/logs storage/api-docs && chmod -R ug+rwX bootstrap/cache storage/framework storage/logs storage/api-docs'

docker compose exec -T app composer install --no-interaction --prefer-dist

docker compose exec -T app php artisan key:generate --force

docker compose exec -T app php artisan migrate:fresh --seed --force

docker compose exec -T app php artisan l5-swagger:generate

New-Item -ItemType File -Path .setup-complete -Force | Out-Null
```

## Common Issues

### Port 8000 is already allocated

Example error:

```txt
Error response from daemon: failed to set up container networking: Bind for 0.0.0.0:8000 failed: port is already allocated
```

Cause:

Another application or another instance of the project is already using port `8000`.

Solution:

Change `APP_PORT` in `.env`, for example:

```env
APP_PORT=8001
```
