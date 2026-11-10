# IT341 Final Project Starter: Laravel 12 REST API

This is the starting point for your IT341 final project. It gives you a working Laravel 12 API with token authentication, so you can spend your 3 weeks on **your** app.

> **Replace this README** with your own before you submit (keep the setup steps and add your endpoint table). See "What your README must contain" at the bottom.

## What is already here

| Part | Where | Notes |
| --- | --- | --- |
| Token auth (Laravel Sanctum) | `app/Http/Controllers/Api/AuthController.php` | register, login, logout, current user |
| Example resource: Notes | `Note` model, `NoteController`, `StoreNoteRequest`, `UpdateNoteRequest`, `NoteResource`, `NotePolicy` | A full CRUD pattern to copy. It does **not** count toward your 3 required models |
| JSON-only API errors | `bootstrap/app.php`, `app/Http/Middleware/ForceJsonResponse.php` | 401, 403, 404 and 422 always come back as JSON |
| Feature tests | `tests/Feature/AuthTest.php`, `tests/Feature/NoteApiTest.php` | 16 passing tests to learn from |
| Demo data | `database/seeders/DatabaseSeeder.php` | Demo login: `demo@example.com` / `password` |
| Deployment notes | `DEPLOYMENT.md` | Fill in when you deploy (CLO 3) |
| Product requirements | `PRD.md` | Write this first, due day 3 of week 1 |
| AI usage log | `PROMPTS.md` | Fill it in after **every** commit |
| Design decisions | `DECISIONS.md` | Write it yourself, without AI |
| N+1 query guard | `app/Providers/AppServiceProvider.php` | Lazy loading throws an error outside production |
| CI | `.github/workflows/tests.yml` | Runs Pint (code style) and `php artisan test` on every push |

## Requirements

- PHP 8.2 or newer (with `pdo_sqlite`, `mbstring`)
- Composer 2
- SQLite (default, nothing to install) or MySQL if you prefer

## Setup

```bash
# 1. Create your own repo from this template ("Use this template" on GitHub), then:
git clone https://github.com/<you>/<your-repo>.git
cd <your-repo>

# 2. Install and configure
composer install
cp .env.example .env
php artisan key:generate

# 3. Build the database with demo data
touch database/database.sqlite        # Windows PowerShell: ni database/database.sqlite
php artisan migrate:fresh --seed

# 4. Run the tests and the style check, then start the API
php artisan test
./vendor/bin/pint --test              # ./vendor/bin/pint fixes the style for you
php artisan serve                     # http://localhost:8000
```

To use MySQL instead, set `DB_CONNECTION=mysql` and the `DB_*` values in `.env`.

## Using the API

Every endpoint is under `/api`. Send `Accept: application/json`, and for protected routes `Authorization: Bearer <token>`.

```bash
# Log in and get a token
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -d email=demo@example.com -d password=password

# Use the token
curl http://localhost:8000/api/notes \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc..."
```

### Endpoints in the starter

| Method | URL | Auth | What it does |
| --- | --- | --- | --- |
| POST | `/api/register` | no | Create an account. Body: `name`, `email`, `password`, `password_confirmation`. Returns user + token |
| POST | `/api/login` | no | Body: `email`, `password`. Returns user + token |
| POST | `/api/logout` | yes | Revokes the current token |
| GET | `/api/user` | yes | The logged-in user |
| GET | `/api/notes` | yes | Your notes, 10 per page (`?page=2`) |
| POST | `/api/notes` | yes | Create. Body: `title` (required), `body`, `is_pinned` |
| GET | `/api/notes/{id}` | yes | One of your notes (403 if not yours) |
| PUT/PATCH | `/api/notes/{id}` | yes | Update any of the fields |
| DELETE | `/api/notes/{id}` | yes | Delete (204) |

Run `php artisan route:list --path=api` to see every route.

### Calling the API from your frontend

```js
const API = 'http://localhost:8000/api';

const res = await fetch(`${API}/login`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
  body: JSON.stringify({ email, password }),
});
const { token } = await res.json();
localStorage.setItem('token', token);

// Later requests
const notes = await fetch(`${API}/notes`, {
  headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
}).then(r => r.json());
// A 422 response has { message, errors: { field: ["..."] } }; show those errors in your form.
```

Your frontend must be **Vue.js or React**. CORS is open during development so it can call the API from another port; in production set `CORS_ALLOWED_ORIGINS` to your frontend's URL. Login and register are rate limited, and tokens expire after `SANCTUM_EXPIRATION` minutes (default 1 day).

## Your first step: PRD.md

Before building anything of your own, fill in `PRD.md`: the problem, your users and roles, user stories, business rules, data model, API plan and screens. Commit it, log it in `PROMPTS.md`, and wait for feedback before you create your models.

## How to add your own resource (the pattern to follow)

Follow the Notes example, one step and one commit at a time:

1. `php artisan make:model Product -mf` (model, migration, factory). Add relationships.
2. `php artisan make:request StoreProductRequest` and `UpdateProductRequest`. Add rules.
3. `php artisan make:resource ProductResource`. Choose the fields to return.
4. `php artisan make:policy ProductPolicy --model=Product`. Decide who can do what.
5. `php artisan make:controller Api/ProductController --api --model=Product`. Register it in `routes/api.php` with `Route::apiResource`.
6. `php artisan make:test ProductApiTest`. Test success, validation and authorization.
7. Add it to `DatabaseSeeder`.

Log each step in `PROMPTS.md` and commit. You need at least 40 commits on at least 12 different days; check with `git log --format=%ad --date=short | sort -u | wc -l`.

## What your README must contain (before you submit)

- Project name and a short description of what it does (link to `PRD.md` for the details)
- Setup steps (you can keep the ones above) and the demo login
- A demo login for each role
- Your ERD (`docs/erd.png`)
- A table of **every** endpoint: method, URL, auth and role needed, what it does
- Your chosen level-up features, and where their code and tests are
- Link to your frontend repo (if separate) and your demo video
- Your live HTTPS URLs for the API and the frontend (required)
