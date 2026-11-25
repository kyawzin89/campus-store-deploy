# DEPLOYMENT.md: deployment, security and performance (CLO 3)

## 1. Live URLs

- **Frontend & Backend (Monolith):** [https://web-production-cd971162.up.railway.app](https://web-production-cd971162.up.railway.app)
- **API Endpoints:** [https://web-production-cd971162.up.railway.app/api](https://web-production-cd971162.up.railway.app/api)

## 2. Architecture

| Part | Hosted on | Why this choice |
| --- | --- | --- |
| Frontend (Blade + Tailwind CSS) | Railway (`php:8.3-cli` container) | Server-rendered Blade views with Tailwind CDN eliminate Node/Vite build memory limits (`exit code: 137`) during deployment. |
| Laravel 12 API & Web App | Railway (`web-production-cd971162.up.railway.app`) | Custom `Dockerfile` and `start.sh` automate database initialization, migrations, seeding, and HTTP serving on `$PORT`. |
| Database (SQLite 3) | Railway Persistent Volume (`/app/storage/database/database.sqlite`) | Zero-configuration relational storage persisted across container redeploys via a mounted `/app/storage` volume. |

## 3. Environment variables

| Variable | Purpose |
| --- | --- |
| `APP_NAME` | Application display title (`Campus Store System`) |
| `APP_ENV` | Sets runtime environment mode to `production` |
| `APP_DEBUG` | Disables detailed stack traces (`false`) in production for security |
| `APP_KEY` | 32-byte base64 encryption key used for sessions, cookies, and CSRF tokens |
| `APP_URL` | Base production URL (`[https://web-production-cd971162.up.railway.app](https://web-production-cd971162.up.railway.app)`) |
| `DB_CONNECTION` | Configures Laravel database driver to `sqlite` |
| `DB_DATABASE` | Absolute path to persistent SQLite file (`/app/storage/database/database.sqlite`) |
| `SESSION_DRIVER` | Stores authenticated user sessions in the database |
| `CACHE_STORE` | Stores application cache entries in the database |

## 4. Local Setup & Verification

1. Run `composer install` and `cp .env.example .env`.
2. Generate an application key with `php artisan key:generate`.
3. Create `database/database.sqlite` and run `php artisan migrate --seed`.
4. Start the local server with `php artisan serve` or verify tests with `php artisan test`.
