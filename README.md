# Türkmen Sözlük

Mobile-first Laravel app for a community-driven democratic dictionary of Türkmen slang.

## Features

- Public browsing and search for slang words/phrases.
- Authenticated suggestions for entries and definitions.
- One upvote per user per definition.
- Definitions ordered by community votes.
- Leaderboard by contribution count and received votes.
- Admin hiding for inappropriate entries or definitions.
- Community reports, public moderation log, appeal voting, rules page, and JSON export to reduce capture risk.
- SQLite dictionary import into PostgreSQL.
- Inline click-to-lookup dictionary meanings.

## Local Docker Run

```bash
cp .env.example .env
docker compose up --build
```

Open `http://localhost:8080`.

## Dictionary Import

First inspect the uploaded SQLite file:

```bash
php artisan dictionary:import-sqlite storage/app/dictionary.sqlite --inspect
```

For example: 
```bash
php artisan dictionary:import-sqlite storage/app/turkmen.sqlite --inspect
```

Then import with the mapped table and columns:

```bash
php artisan dictionary:import-sqlite storage/app/dictionary.sqlite \
  --table=words \
  --word=headword \
  --meaning=definition
```

For example:
```bash
php artisan dictionary:import-sqlite storage/app/turkmen.sqlite \
  --table=words \
  --word=word \
  --meaning=definitions
```

Or seed PostgreSQL from the committed SQLite file:

```bash
php artisan db:seed
```

Docker startup runs migrations and this seeder automatically.

The import is idempotent by normalized headword. Manual aliases for inflected word forms can be added with:

```bash
php artisan dictionary:add-alias kitap kitaby
```

Aliases resolve after exact headword lookup and before returning “not found”.

## Coolify

Use the included `Dockerfile` and attach a PostgreSQL service. Set the environment variables from `.env.example`, especially `APP_KEY`, `APP_URL`, and the `DB_*` values. The container entrypoint runs migrations and caches Laravel config/routes/views on startup.

## Community Governance

Moderation is designed to avoid one-person control:

- Active users can report visible entries and definitions.
- Content auto-hides after the configured report threshold.
- Emergency admin hides are public and appealable.
- Authors can appeal hidden content.
- Eligible users can vote to restore appealed content.
- `/governance/log` shows public moderation events.
- `/export.json` exports visible community data so the dictionary can be preserved or forked if governance is captured.

## Tests

```bash
composer install
php artisan test
```
