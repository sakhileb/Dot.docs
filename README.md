<p align="center">
  <img src="public/branding/dot_doc.png" alt="Dot.docs" width="120" />
</p>

<h1 align="center">Dot.docs</h1>

<p align="center">
  <strong>AI-powered collaborative document platform built on Laravel, Livewire &amp; Jetstream</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white" alt="PHP 8.3" />
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/Livewire-3-FB70A9?logo=livewire&logoColor=white" alt="Livewire 3" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3-38BDF8?logo=tailwindcss&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/tests-111%20passed-22c55e" alt="Tests" />
  <img src="https://img.shields.io/badge/license-MIT-blue" alt="License" />
</p>

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Getting Started](#getting-started)
- [Environment Variables](#environment-variables)
- [Database](#database)
- [Running Tests](#running-tests)
- [Artisan Commands](#artisan-commands)
- [CI / CD](#ci--cd)
- [Deployment](#deployment)
- [Security](#security)
- [Project Structure](#project-structure)
- [License](#license)

---

## Overview

Dot.docs is a full-featured collaborative document management platform. It combines an AI-assisted rich-text editor, real-time collaboration, version control, citation management, PDF/Word export, cloud storage integration, analytics, and fine-grained access control into a single cohesive application.

The entire authenticated experience uses a consistent brand system — sky-blue gradients, glass-morphism cards, pill-shaped controls, and a dark-mode-ready Tailwind CSS design token layer.

---

## Features

### Document Editor
- **Rich-text editing** via Quill 2 with tables, image resize, syntax highlighting, and voice dictation
- **Suggestion mode** — inline AI rewrite proposals that reviewers can accept or reject
- **Autosave** with configurable version retention (`DOCUMENT_AUTOSAVE_KEEP`)
- **Version history** — full diff, restore, and metadata per snapshot
- **Milestone / status tracking** inline in the editor header

### AI Capabilities
- **AI Assistant** — inline suggestions, completions, and rewrites via OpenAI GPT-4.1 or Anthropic Claude
- **AI Document Generator** — generate a full draft from a single prompt
- **Plagiarism detection** — content similarity checking
- **AI Analytics** — cache hit rates, operation counts, daily usage volume, and failure tracking
- **Configurable providers** — swap between OpenAI and Anthropic via environment variables

### Collaboration
- **Comments & Reviews** — threaded comments with `@mention` notifications, resolve/reopen, and reply chains
- **Formal review workflow** — request reviews by type (General, Legal, Technical, Editorial) with approve/reject decisions
- **Real-time presence** — cursor positions and online indicators via Pusher / Laravel Echo
- **Document sharing** — per-user permissions (view / comment / edit), public links with optional password, domain restriction, and expiry

### Templates
- **Template Library** — searchable, filterable by category and scope (team / public)
- **Template versioning** — full version history per template
- **Import / Export** — JSON import from external sources, bulk export
- **Sharing** — publish templates publicly across teams

### Document Management
- **Library** — grid and list views, full-text search, filters by status/team, favorites, and folder organisation
- **Folders** — nested user folders with drag-and-drop document assignment
- **Bulk operations** — multi-select delete and move
- **Advanced search** — Boolean query with field targeting

### Export & Transfer (Exchange Hub)
- **Formats** — PDF (DomPDF), Word (PHPWord), Markdown, HTML, and plain text
- **Cloud storage** — Google Drive, Dropbox, and OneDrive import/export
- **Import** — upload existing `.docx` or Markdown files directly into the platform

### Citations
- **Manual citation entry** — title, authors, year, URL, and formatted citation text
- **Zotero / Mendeley import** — paste exported JSON; citations are parsed and attached to the document
- **Copy formatted citation** — one-click clipboard copy

### Mail Merge & Forms
- **Mail Merge** — attach a CSV contact list to a document, preview merged output per contact, and bulk-export a personalised copy for every row
- **Form Builder** — add fillable fields (text, checkbox, date) to a document and collect responses

### Teams & Workspace
- **Multi-team** via Jetstream — create, invite, role-assign, and remove members
- **Team Document Dashboard** — team-scoped document stats and activity summaries
- **Team Analytics** — member count, document count, storage usage with visual bar, and recent activity feed
- **Webhook Management** — configure outbound webhook endpoints for team events (Slack, Discord, custom HTTP)
- **Storage quotas** per team

### Analytics & Observability
- **Product Intelligence Dashboard** — provider-level AI usage, 5 KPI tiles, integration status grid
- **Personal Dashboard** — per-user document count, recent edits, AI usage, activity trend
- **Audit Logs** — every sensitive action logged with actor, IP, and payload
- **Laravel Telescope** — enabled in development and staging for query, request, and job inspection
- **Sentry** — error tracking with configurable trace and profile sample rates

### Security
- **Two-factor authentication** (TOTP) via Jetstream
- **API tokens** via Laravel Sanctum with granular permission scopes
- **Rate limiting** on sensitive actions via `throttle:sensitive-actions` middleware
- **Audit middleware** (`audit.sensitive`) on all protected routes
- **Encrypted document content** at rest via artisan command
- Standard Laravel/OWASP protections: CSRF, XSS escaping, parameterised queries, CSP

### Privacy / GDPR
- **Data export** — authenticated users can request a full personal data export
- **Retention enforcement** — configurable document retention policies via artisan command
- Public `/privacy` policy page and authenticated `/privacy/rights` page

### Progressive Web App
- **PWA manifest** — installable on desktop and mobile
- **Service worker** with offline-first caching strategy
- **Offline sync** — edits queued locally and reconciled on reconnect via `/api/documents/{id}/sync`

### Notifications
- **Granular preferences** — per-category toggles (Comments, Reviews, Mentions, Team Events, AI Suggestions) with email, in-app, and push channels

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 |
| Framework | Laravel 13 |
| Auth & Teams | Laravel Jetstream 5 + Sanctum 4 |
| Reactive UI | Livewire 3 + Alpine.js 3 |
| Styling | Tailwind CSS 3 + custom brand token classes |
| Rich-text editor | Quill 2 + quill-table-better + quill-image-resize-module |
| AI | openai-php/client (OpenAI) + Anthropic via HTTP |
| PDF export | barryvdh/laravel-dompdf |
| Word export | phpoffice/phpword |
| HTML→Markdown | league/html-to-markdown |
| Real-time | Pusher + Laravel Echo |
| Error tracking | Sentry (`sentry/sentry-laravel`) |
| Observability | Laravel Telescope |
| Asset pipeline | Vite 8 + laravel-vite-plugin |
| Database | SQLite (dev) / PostgreSQL or MySQL (production) |
| Queue | Database driver (Redis-compatible) |
| Tests | PHPUnit via `php artisan test` — 111 passing |

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/          # Thin controllers (analytics, privacy, PWA, offline sync, API)
│   └── Middleware/           # Rate limiting, audit logging
├── Livewire/
│   ├── Documents/            # Editor, Index, Search, Reviews, ShareManager, CitationManager,
│   │                         #   Transfer, Versions, MailMerge, FormBuilder, AiGenerator,
│   │                         #   AiAssistant, SharedView, EditorLazyLoader
│   ├── Templates/            # Library (search, CRUD, import/export, preview)
│   ├── Teams/                # TeamDocumentDashboard, TeamAnalytics, TeamActivityFeed,
│   │                         #   WebhookManagement
│   ├── Dashboard/            # PersonalDashboard
│   ├── Profile/              # NotificationPreferences
│   └── AI/                   # Analytics
├── Models/                   # 20+ Eloquent models
└── Services/                 # AI, Backup/Restore, Analytics, Retention

resources/
├── css/app.css               # Brand token classes (.app-shell, .app-card, .app-pill-button, …)
├── js/                       # Alpine, Quill, Echo/Pusher bootstrap, Service Worker
└── views/
    ├── layouts/              # app.blade.php — authenticated shell
    ├── components/           # Branded Jetstream UI components
    ├── livewire/             # Livewire template partials (109 Blade files total)
    ├── documents/            # Wrapper pages (edit, versions, reviews, share, citations, …)
    ├── templates/            # Template library wrapper
    ├── teams/                # Team dashboard, analytics, activity, webhooks, settings
    ├── profile/              # Account settings, notification preferences
    ├── api/                  # API token management
    ├── analytics/            # Product intelligence dashboard
    ├── ai/                   # AI analytics
    └── privacy/              # GDPR pages
```

---

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+ and npm
- SQLite (bundled with PHP, for local development) or PostgreSQL / MySQL

### Installation

```bash
# 1. Clone
git clone https://github.com/sakhileb/Dot.docs.git
cd Dot.docs

# 2. PHP dependencies
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Database
touch database/database.sqlite
php artisan migrate

# 5. Frontend
npm install
npm run build

# 6. Serve
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000).

### Development (watch mode)

```bash
npm run dev       # Vite HMR
php artisan serve # in a second terminal
```

---

## Environment Variables

All variables with defaults are documented in `.env.example`. Key variables:

| Variable | Description | Default |
|---|---|---|
| `APP_NAME` | Application display name | `Laravel` |
| `APP_URL` | Public URL | `http://localhost` |
| `DB_CONNECTION` | Database driver | `sqlite` |
| `OPENAI_API_KEY` | OpenAI API key | — |
| `OPENAI_MODEL` | Default chat model | `gpt-4.1-mini` |
| `ANTHROPIC_API_KEY` | Anthropic API key (alternate AI provider) | — |
| `ANTHROPIC_MODEL` | Anthropic model | `claude-3-5-sonnet-latest` |
| `AI_RATE_LIMIT_PER_MINUTE` | Max AI requests per user per minute | `20` |
| `AI_CACHE_TTL_MINUTES` | Cache duration for identical AI prompts | `60` |
| `DOCUMENT_AUTOSAVE_KEEP` | Number of autosave versions to retain | `50` |
| `PUSHER_APP_ID` | Pusher app ID (real-time collaboration) | — |
| `PUSHER_APP_KEY` | Pusher app key | — |
| `PUSHER_APP_SECRET` | Pusher app secret | — |
| `GOOGLE_DRIVE_ACCESS_TOKEN` | OAuth token for Google Drive | — |
| `DROPBOX_ACCESS_TOKEN` | Dropbox API token | — |
| `ONEDRIVE_ACCESS_TOKEN` | Microsoft OneDrive token | — |
| `SENTRY_LARAVEL_DSN` | Sentry DSN for error tracking | — |
| `TELESCOPE_ENABLED` | Enable Laravel Telescope | `true` |
| `ANALYTICS_PROVIDER` | Product analytics backend | `plausible` |
| `PLAUSIBLE_DOMAIN` | Plausible site domain | — |
| `GOOGLE_ANALYTICS_MEASUREMENT_ID` | GA4 measurement ID | — |
| `WEBHOOKS_ENABLED` | Enable outbound team webhooks | `true` |

---

## Database

Schema is managed via 35 Laravel migrations in `database/migrations/`. Key tables:

| Table | Purpose |
|---|---|
| `documents` | Core document records |
| `document_versions` | Autosave and manual snapshot history |
| `document_comments` | Threaded comments with suggestion/reply support |
| `document_reviews` | Formal review requests and decisions |
| `document_shares` | User and public-link share grants with analytics |
| `document_cursors` | Real-time collaborative cursor positions |
| `templates` / `template_versions` | Template library with full versioning |
| `citation_references` | Per-document citations (manual + Zotero/Mendeley) |
| `ai_suggestions` | AI suggestion records with queue tracking |
| `document_export_jobs` | Async export job tracking |
| `audit_logs` | Tamper-evident sensitive-action audit trail |
| `activity_logs` | General user activity feed |
| `automation_webhooks` | Team webhook endpoint configuration |
| `user_notification_preferences` | Per-user, per-channel notification settings |
| `team_storage_quotas` | Per-team storage allocation and usage |
| `user_folders` / `folder_documents` | Custom folder hierarchy |
| `document_form_fields` | Fillable field definitions for Form Builder |

---

## Running Tests

```bash
# Full suite
php artisan test

# Single file
php artisan test tests/Feature/DocumentOperationsTest.php

# Filter by name
php artisan test --filter=canCreateDocument
```

Current status: **111 passed, 4 skipped** (email-verification and registration-disable skips are expected — those Jetstream features are toggled off in `.env.example`).

### Test coverage areas

| Category | Tests |
|---|---|
| Authentication & 2FA | Registration, login, password reset, TOTP |
| Document operations | CRUD, versions, search |
| Editor | Voice dictation, WCAG accessibility labels |
| AI features | Generator mock, assistant, plagiarism detection |
| Cloud integrations | Google Drive, Dropbox, OneDrive import/export |
| Collaboration | Comments, reviews, share manager |
| Citations | Manual entry, Zotero/Mendeley import |
| Mail merge & forms | Field rendering, merge output |
| Teams | Create, invite, roles, leave, delete, webhooks |
| Profile & API tokens | CRUD, Sanctum scoped permissions |
| Privacy / GDPR | Data export, rights page routes |
| Security | Middleware, penetration, SQL injection |
| Analytics | Dashboard render, AI stats |
| Performance | Document index response time budget |
| Observability | Sentry integration |

---

## Artisan Commands

```bash
# Encrypt all document content at rest
php artisan documents:encrypt-content

# Create a labelled backup archive
php artisan backup:create --label=manual --cleanup

# Verify integrity of an existing backup
php artisan backup:verify backups/<file>.tar.gz

# Export a user's personal data (GDPR Article 20)
php artisan privacy:export-user <userId>

# Enforce document retention policy and purge expired records
php artisan privacy:enforce-retention
```

---

## CI / CD

Three GitHub Actions workflows are in `.github/workflows/`:

### `ci.yml` — Continuous Integration

Triggers on every push and pull request to `main`.

1. Checkout + setup PHP 8.3 + Composer install
2. Copy `.env.example` → `.env`, generate app key, create SQLite DB
3. `php artisan migrate`
4. `php artisan test`
5. `npm ci && npm run build`

### `deploy-staging.yml` — Staging Deployment

Trigger: manual (`workflow_dispatch`).

Required GitHub secrets: `STAGING_SSH_PRIVATE_KEY`, `STAGING_HOST`, `STAGING_USER`, `STAGING_APP_PATH`.

### `deploy-production.yml` — Production Deployment

Trigger: manual (`workflow_dispatch`).

Required GitHub secrets: `PROD_SSH_PRIVATE_KEY`, `PROD_HOST`, `PROD_USER`, `PROD_APP_PATH`.

---

## Deployment

Both staging and production workflows follow the same guarded migration strategy:

1. Put app into maintenance mode (`php artisan down`)
2. Create a pre-deploy backup (`php artisan backup:create`)
3. Pull latest `main` from GitHub
4. `composer install --no-dev --optimize-autoloader`
5. `npm ci && npm run build`
6. `php artisan migrate --force`
   - On failure → `php artisan migrate:rollback --step=1 --force` then abort
7. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
8. Bring app back up (`php artisan up`)

A staging environment template is provided at `.env.staging.example`.

### Telescope

- Enable with `TELESCOPE_ENABLED=true` in local and staging environments.
- Disable in production unless actively debugging.

---

## Security

| Concern | Approach |
|---|---|
| Authentication | Jetstream — email/password + optional 2FA TOTP |
| API auth | Sanctum bearer tokens with scoped permissions |
| CSRF | Laravel CSRF tokens on all forms |
| XSS | Blade auto-escaping throughout; CSP headers |
| SQL injection | Eloquent parameterised queries throughout |
| Rate limiting | `throttle:sensitive-actions` middleware on auth routes |
| Audit trail | `audit.sensitive` middleware — actor + IP logged per action |
| Encrypted content | `documents:encrypt-content` command for data at rest |
| Dependency hygiene | Composer and npm lock files committed |
| Error reporting | Sentry with configurable trace and profile sample rates |

Report security vulnerabilities privately via [GitHub Security Advisories](https://github.com/sakhileb/Dot.docs/security/advisories/new).

---

## Project Structure — Quick Reference

```
.
├── app/                   PHP application code
│   ├── Http/              Controllers (6) + Middleware
│   ├── Livewire/          24 reactive Livewire component classes
│   ├── Models/            20+ Eloquent models
│   └── Services/          AI, Backup/Restore, Analytics, Retention
├── config/                Laravel configuration
├── database/
│   ├── migrations/        35 migration files
│   └── database.sqlite    Local dev database (git-ignored)
├── public/
│   └── branding/          Dot.docs logo assets
├── resources/
│   ├── css/app.css        Brand token CSS classes
│   ├── js/                Alpine, Quill, Echo, Service Worker
│   └── views/             109 Blade templates
├── routes/web.php         All application routes
├── storage/               Logs, cache, backups (git-ignored)
├── tests/                 43 feature tests + 6 unit tests
├── .github/workflows/     CI, staging, and production pipelines
├── .env.example           All supported environment variables
└── .env.staging.example   Staging environment defaults
```

---

## License

Dot.docs is open-sourced software licensed under the [MIT license](LICENSE).
