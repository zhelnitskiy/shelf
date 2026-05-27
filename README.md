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

For the first-time project setup:

```bash
make setup
```

Native Windows setup is supported through PowerShell only:

```powershell
.\setup.ps1
```

`setup` is intended for one-time initialization and creates `.setup-complete` after success. Remove `.setup-complete` manually to run setup again.

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