# Project Manager API

A RESTful API for managing projects, teams, and task tracking. Built with **Laravel 11** and **PHP 8.3**, it provides role-based access control, OAuth2 authentication, and hierarchical incidence management.

## Features

- **Project management** — create projects, manage members, and track summaries
- **Incidence tracking** — tasks with types, priorities, states, and parent/child hierarchy
- **Role-based access control** — per-project roles (Administrator, Project Gestor, Developer, User) with granular permission schemes
- **OAuth2 authentication** — via Laravel Passport with personal access tokens
- **Two-factor authentication** — TOTP with QR code setup and recovery codes
- **Email verification & password reset**
- **Teams & boards** — group users and organize projects
- **API documentation** — auto-generated via Scribe

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11.9 |
| Language | PHP 8.3 |
| Authentication | Laravel Passport (OAuth2) |
| Authorization | Spatie Laravel Permission |
| Database | MySQL 8.0 |
| Cache / Queue | Redis |
| Web server | Nginx (Alpine) |
| Containers | Docker + Docker Compose |
| Testing | Pest PHP |
| API Docs | Scribe |
| Code style | Laravel Pint |

## Requirements

- Docker & Docker Compose
- Git

## Getting Started

### 1. Clone and configure environment

```shell
git clone <repository-url>
cd project-manager-api
bin/app copy      # copies .env.example → .env
bin/app install   # installs Composer and npm dependencies
```

### 2. Start containers

```shell
bin/app build
bin/app up -d
```

### 3. Initialize the application

Runs migrations, seeders, sets `APP_KEY`, and generates Passport keys:

```shell
bin/app artisan app:install
```

The API will be available at `http://localhost` (or the port set in `APP_PORT`).

### 4. Stop containers

```shell
bin/app stop
```

## Development Commands

All commands run inside the Docker container via the `bin/app` CLI.

| Task | Command |
|---|---|
| Start containers | `bin/app up -d` |
| Stop containers | `bin/app stop` |
| Run Artisan | `bin/app artisan <command>` |
| Install Composer package | `bin/app composer require <package>` |
| Install npm package | `bin/app npm install <package>` |
| Format code | `bin/app pint` |
| Run tests | `bin/app test` |
| Regenerate API docs | `bin/app artisan scribe:generate` |
| See all commands | `bin/app -h` |

## API Overview

All routes are prefixed with `/api`. Authenticated routes require a `Bearer` token obtained via `POST /api/auth/login`.

### Authentication — `/api/auth`

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/auth/register` | Register a new user | — |
| POST | `/auth/login` | Obtain access token | — |
| POST | `/auth/logout` | Revoke token | Required |
| POST | `/auth/forgot-password` | Send reset email | — |
| POST | `/auth/reset-password` | Reset password | — |
| POST | `/auth/two-factor-challenge` | Complete 2FA challenge | — |
| GET | `/auth/email/verify/{id}/{hash}` | Verify email address | — |
| POST | `/auth/email/verification-notification` | Resend verification email | Required |

### User Profile — `/api/user`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/user/profile` | Get authenticated user profile |
| PATCH | `/user/profile` | Update profile |
| POST | `/user/two-factor/authentication` | Enable 2FA |
| DELETE | `/user/two-factor/authentication` | Disable 2FA |
| GET | `/user/two-factor/qr-code` | Get 2FA QR code |
| GET | `/user/two-factor/recovery-codes` | List recovery codes |
| POST | `/user/two-factor/recovery-codes` | Regenerate recovery codes |

### Projects — `/api/projects`

| Method | Endpoint | Description | Role required |
|---|---|---|---|
| GET | `/projects` | List user's projects | — |
| POST | `/projects` | Create project | — |
| GET | `/projects/{project}` | Get project details | — |
| PUT/PATCH | `/projects/{project}` | Update project | Admin |
| DELETE | `/projects/{project}` | Delete project | Admin |
| GET | `/projects/{project}/summary` | Project statistics | — |
| GET | `/projects/{project}/unassigned-users` | Users not yet in project | — |

### Project Members — `/api/projects/{project}/members`

| Method | Endpoint | Description | Role required |
|---|---|---|---|
| GET | `/members` | List members | Admin |
| GET | `/members/{member}` | Get member details | Admin |
| POST | `/members` | Add member | Admin |
| PATCH | `/members/{member}/role` | Change member role | Admin |
| DELETE | `/members/{member}` | Remove member | Admin |

### Incidences (Tasks) — `/api/projects/{project}/incidences`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/incidences` | List project incidences |
| POST | `/incidences` | Create incidence |
| GET | `/incidences/{incidence}` | Get incidence details |
| PUT | `/incidences/{incidence}/update` | Update incidence |
| DELETE | `/incidences/{incidence}` | Delete incidence |
| GET | `/incidences/{incidence}/assignment` | Get assignment |
| POST | `/incidences/{incidence}/assignment` | Assign incidence |
| PUT | `/incidences/{incidence}/assignment` | Update assignment |
| DELETE | `/incidences/{incidence}/assignment` | Remove assignment |

### Teams — `/api/teams`

Standard resource routes: `GET`, `POST`, `GET /{team}`, `PUT /{team}`, `DELETE /{team}`.

### Admin — `/api/auth/users`

User CRUD available only to users with the **Admin** role.

## Project Structure

```
project-manager-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Request handlers, organized by domain
│   │   ├── Middleware/        # CheckProjectAdmin, CheckRole, etc.
│   │   ├── Requests/          # Validated request DTOs
│   │   └── Resources/         # API response transformers
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic
│   ├── Actions/               # Single-responsibility action classes
│   └── Enums/                 # PHP enums for types, states, priorities
├── routes/
│   └── api/
│       ├── api.php            # Route aggregator
│       ├── _auth.php          # Auth routes
│       ├── _user.php          # Profile routes
│       └── _app.php           # Project / incidence routes
├── database/
│   ├── migrations/
│   └── seeders/
├── infrastructure/
│   ├── app/                   # PHP-FPM Dockerfile & config
│   ├── nginx/                 # Nginx config
│   └── queues/                # Supervisor config for workers
├── tests/                     # Pest test suites
├── docker-compose.yml
└── bin/app                    # Docker CLI helper
```

## Writing Code

### Code Style

Follow the [Spatie PHP/Laravel guidelines](https://spatie.be/guidelines/laravel-php). Configure your editor with `.editorconfig` and run Pint before committing:

```shell
bin/app pint
```

### Testing

Use **Pest PHP**. Follow a TDD approach:

1. Run existing tests on a fresh branch — all must pass.
2. Write failing tests for the new behaviour.
3. Write the minimum code to make them pass.
4. Run the full suite — all must pass.
5. Refactor and rerun.

```shell
bin/app test
```

### API Documentation

Routes are grouped using `#[Group]` and `#[Subgroup]` attributes on controllers. After adding an endpoint, regenerate the docs:

```shell
bin/app artisan scribe:generate
```

Organize routes in domain-specific files under `routes/api/` (e.g. `routes/api/_inventory.php`) and include them in `routes/api/api.php`.

Each controller must declare its group and subgroup so the generated docs stay readable:

```php
#[Group('Auth')]
#[Subgroup('Email Verification')]
#[Authenticated]
class EmailVerificationController extends Controller
{
    /**
     * Resend verification email
     *
     * Send a new email verification notification.
     */
    public function send(Request $request): JsonResponse { ... }
}
```
