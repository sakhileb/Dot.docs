<div align="center">

<img src="docs/logo.svg" alt="Dot.docs" width="320" />

<br /><br />

**Create, edit, organise, and share documents across your team in real time.**

<br />

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white) ![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white) ![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?style=flat-square) ![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?style=flat-square&logo=postgresql&logoColor=white)

<br /><br />

**Part of the [InfoDot Ecosystem](https://github.com/sakhileb/InfoDot)** &nbsp;·&nbsp; `docs.infodot.app`

</div>

---

## What is Dot.docs?

Dot.docs is the team document platform in the InfoDot ecosystem. Rich-text editing, real-time collaboration, and smart organisation keep your team's knowledge structured and accessible — with full version history and granular sharing controls.

## Core Features

- Rich-text editor with formatting, tables, and embeds
- Real-time multiplayer editing via Laravel Reverb
- Document versioning with named snapshots
- Folder and tag-based organisation
- Template library — start from pre-built layouts
- Comments and inline suggestions with resolution tracking
- Export to PDF, Markdown, and plain text
- Ecosystem SSO from InfoDot hub

## Domain Models

- **Document** — rich-text content with metadata
- **DocumentVersion** — snapshot history
- **DocumentComment** — inline annotation thread
- **DocumentTemplate** — reusable layouts

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.4 |
| Frontend | Livewire 3 · Alpine.js 3 · Tailwind CSS |
| Database | PostgreSQL 16 (shared across ecosystem) |
| Realtime | Laravel Reverb |
| Auth | Laravel Sanctum (InfoDot SSO) |
| AI | Anthropic Claude (`claude-sonnet-4-6`) |
| Storage | AWS S3 / Local (Flysystem) |
| Search | Laravel Scout · Meilisearch |
| Queue | Redis · Laravel Horizon |

## Quick Start

```bash
git clone https://github.com/sakhileb/Dot.docs.git
cd Dot.docs
cp .env.example .env
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate
php artisan serve
```

> **Ecosystem SSO:** Set `DB_*` env vars to the shared InfoDot PostgreSQL instance and `APP_URL=https://docs.infodot.app`. Users authenticated through InfoDot gain access automatically via Sanctum handoff tokens.

## Ecosystem

**Dot.docs** is one of **21 platforms** in the InfoDot ecosystem, connected via shared PostgreSQL and Sanctum SSO. Visit [InfoDot](https://github.com/sakhileb/InfoDot) to explore the full platform map.

## License

MIT © [SK Digital / BluPin Incorporated](https://github.com/sakhileb)
