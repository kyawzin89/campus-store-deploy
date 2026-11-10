# DEPLOYMENT.md: deployment, security and performance (CLO 3)

You will explain this file in the defense. Write the reasons yourself.

## 1. Live URLs

- **Frontend:** https://...
- **API:** https://.../api

## 2. Architecture

_Diagram of the deployed system (`docs/deployment.png`): browser, frontend host, API host, database, and any queue, cache or storage service._

| Part | Hosted on | Why this choice |
| --- | --- | --- |
| Frontend (Vue/React) | | |
| Laravel API | | |
| Database | | |

## 3. Environment variables

_Names only, never values._

| Variable | Purpose |
| --- | --- |
| `APP_KEY` | Encryption key |
| `DB_*` | Database connection |
| `CORS_ALLOWED_ORIGINS` | Frontend URL allowed to call the API |
| | |

## 4. Security controls

| Control | How it is applied | Why it matters for this app |
| --- | --- | --- |
| `APP_DEBUG=false`, `APP_ENV=production` | | |
| HTTPS only | | |
| CORS limited to the frontend | | |
| Rate limiting | | |
| Token expiry | | |
| No sensitive fields in responses | | |
| | | |

## 5. Performance optimizations

| Optimization | Where | Why |
| --- | --- | --- |
| Eager loading (no N+1) | | |
| Database indexes | | |
| Cache for _which read?_ | | |
| `php artisan optimize`, `composer install --no-dev -o` | | |
| Minified frontend build | | |

## 6. Measurement

_One optimization measured before and after. Say how you measured it (e.g. Laravel Debugbar or Telescope query count, browser network tab, `curl -w "%{time_total}"`)._

| Endpoint | Before | After | How measured |
| --- | --- | --- | --- |
| | | | |

## 7. Deployment steps

_The exact commands or settings you used, so someone else could redeploy it._
