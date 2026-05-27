# Shelf

REST API application for a book library.

## Stack

- PHP 8.5
- Laravel 13
- Docker / Docker Compose
- MySQL

## Status

Initial project bootstrap. The application skeleton and Docker environment are prepared.

## Run

```bash
make up
```

## Seed Demo Data

Use a fresh database when generating demo data:

```bash
make seed-fresh
```

`GenreSeeder` is idempotent because genres are a fixed catalog. `AuthorSeeder`, `PublisherSeeder`, and `BookSeeder` generate demo data and intentionally expect empty tables.
