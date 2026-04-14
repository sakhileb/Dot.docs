<p align="center">
  <img src="dot_doc.png" width="220" alt="dot.doc Logo" />
</p>

<h1 align="center">dot.doc</h1>
<p align="center"><strong>AI-Powered Collaborative Document Creation Platform</strong></p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?logo=laravel" alt="Laravel" />
  <img src="https://img.shields.io/badge/Livewire-3.x-pink?logo=livewire" alt="Livewire" />
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38bdf8?logo=tailwindcss" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/OpenAI-GPT--4o-412991?logo=openai" alt="OpenAI" />
  <img src="https://img.shields.io/badge/Real--time-Laravel%20Reverb-blueviolet" alt="Reverb" />
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License" />
</p>

---

## Overview

**dot.doc** is a full-featured, AI-powered document platform built with Laravel and Livewire. It brings together real-time collaborative editing, an intelligent AI writing assistant, rich version history, and a polished TipTap-based rich text editor — all in a single, team-oriented web application.

Whether you're drafting a business proposal, taking meeting notes, or co-authoring technical documentation, dot.doc gives your team the tools to write faster, collaborate smarter, and never lose a draft.

---

## Table of Contents

- [Features](#features)
  - [Rich Text Editor](#️-rich-text-editor)
  - [Real-time Collaboration](#-real-time-collaboration)
  - [AI Writing Assistant](#-ai-writing-assistant)
  - [AI Command Palette](#️-ai-command-palette-ctrlk--cmdk)
  - [AI Chat Sidebar](#-ai-chat-sidebar)
  - [Version History & Autosave](#-version-history--autosave)
  - [Comments & Suggestions](#-comments--suggestions)
  - [Document Templates](#-document-templates)
  - [Export & Import](#-export--import)
  - [Teams & Permissions](#-teams--permissions)
  - [Notifications](#-notifications)
  - [Offline Mode](#-offline-mode)
  - [Voice Typing](#️-voice-typing)
  - [Webhooks & Plugins](#-webhooks--plugins)
  - [Performance & Security](#-performance--security)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Environment Variables](#environment-variables)
- [Running the Application](#running-the-application)
- [Testing](#testing)
- [License](#license)

---

## Features

### ✏️ Rich Text Editor

dot.doc uses **TipTap** (wrapped in Alpine.js) as its core editing engine, giving writers a powerful and extensible writing surface.

- Full formatting toolbar: **bold**, *italic*, headings (H1–H3), bullet & ordered lists, hyperlinks
- Table support via TipTap Table extension
- Image embedding — upload images directly into documents (served from local storage)
- Native undo/redo via TipTap StarterKit history
- **Autosave** with 1.5-second debounce — changes are saved automatically without any manual action
- Save status indicator and "Last edited by X at Y" display in the editor toolbar

---

### 👥 Real-time Collaboration

Multiple users can edit the same document simultaneously using **Laravel Reverb** WebSockets and **Laravel Echo**.

- **Presence channels** (`document.{id}`) — see who is currently viewing or editing
- Active collaborator avatars displayed in the editor toolbar (overflow badge for 4+ users)
- Live content sync — remote changes are merged into the local editor in real time (own edits are skipped to prevent loops)
- **Typing indicator** — an amber dot shows when a collaborator is actively typing
- User join/leave events broadcast to all participants via `UserJoinedDocument` / `UserLeftDocument`
- Presence state stored in Redis via `PresenceService` for fast lookups

---

### 🤖 AI Writing Assistant

Powered by **OpenAI GPT-4o** (configurable via `OPENAI_MODEL` env), the AI assistant integrates directly into the editor workflow.

| Feature | Description |
|---|---|
| **Grammar & Spell Check** | Sends plain text to AI, returns corrections inline |
| **Summarization** | Generates a TL;DR of ≤150 words from the full document |
| **Continue Writing** | Generates the next paragraph(s) based on existing content |
| **Change Tone** | Rewrites the document in Formal, Casual, Persuasive, or Concise styles |
| **Translate** | Converts the entire document to any specified language |
| **Outline Generation** | Generates a structured outline from a prompt |

Rate limiting is enforced at **20 AI requests per user per hour** to manage API costs.

---

### ⌨️ AI Command Palette (Ctrl+K / Cmd+K)

A fast, keyboard-driven command palette for AI actions — inspired by modern dev tools.

- Triggered globally with `Ctrl+K` (Windows/Linux) or `Cmd+K` (macOS)
- Built-in slash commands:
  - `/summarize` — generate a summary
  - `/grammar` — grammar & spell check
  - `/continue` — continue writing
  - `/tone [style]` — change document tone
  - `/translate [lang]` — translate to a language
  - `/outline` — generate a document outline
- Suggestions list with descriptions shown as you type
- Results displayed in a bottom panel with **Apply** and **Dismiss** actions
- Supports free-form natural language instructions beyond the built-in commands

---

### 💬 AI Chat Sidebar

A context-aware AI chat panel anchored to the document editor.

- Floating button (bottom-right) opens a **420px sliding panel**
- The AI receives the full document content as its system context — no copy-pasting needed
- Maintains up to **20 turns** of conversation history for multi-turn reasoning
- Auto-scrolls to the latest message
- Animated typing indicator (bouncing dots) while the AI is responding

---

### 🕒 Version History & Autosave

Every change is automatically versioned so nothing is ever lost.

- **Auto-snapshots** created by `DocumentObserver` on every content save, recording the author and version number
- **Version Browser** — full-page split layout listing all versions paginated (15 per page) with timestamps, author, and version badge
- **Restore** — copy any historical snapshot back to the current document (auto-increments version)
- **Side-by-side diff view** — word-level highlight using `jfcherng/php-diff` so you can see exactly what changed between any two versions

---

### 💡 Comments & Suggestions

A Google Docs-style layer of collaborative feedback built on top of the editor.

**Comments**

- Threaded comment system tied to the document (and optionally to text selections)
- `@mention` support with live user search
- Resolve / re-open comment threads
- Real-time comment notifications broadcast to all collaborators

**Suggestion Mode (Track Changes)**

- Toggle between **Edit mode** and **Suggestion mode**
- In suggestion mode, edits are stored as pending suggestion records rather than applied directly
- Each suggestion displays a diff highlight — reviewers can **Accept** (merge) or **Reject** (discard) individually

---

### 📄 Document Templates

A template gallery to kickstart any document type.

- Pre-built templates: **Resume**, **Business Proposal**, **Meeting Notes**, **Blog Post**
- Save any document as a reusable **team template**
- Generate a complete document outline from a prompt via the AI `/outline` command

---

### 📤 Export & Import

Get your content in and out of dot.doc in any format you need.

**Export**

| Format | Details |
|---|---|
| PDF | Styled via Blade template, rendered by `barryvdh/laravel-dompdf` |
| Word (.docx) | Generated with `phpoffice/phpword` |
| HTML | Self-contained HTML file download |
| Markdown | Converted with `league/html-to-markdown` |

**Import**

| Format | Details |
|---|---|
| Word (.docx) | Parsed by PHPWord and converted to editor HTML |
| Markdown / Plain Text | Converted via CommonMark |

Export requests are rate-limited to **10 per user per hour**.

---

### 🔐 Teams & Permissions

dot.doc is built around **Laravel Jetstream Teams**, giving organizations fine-grained access control.

- **Roles**: Owner, Admin, Editor, Viewer
- Team-based document ownership and access
- Share documents via **public links** (with optional password and expiration)
- Invite collaborators by email — invitations managed via Jetstream's built-in flow
- `DocumentPolicy` gates all actions: view, update, delete, manage, share
- Two-Factor Authentication (2FA) and profile photo support via Jetstream

---

### 🔔 Notifications

Real-time and async notification delivery for all collaboration events.

- **In-app toast notifications** via Laravel Echo on mention, comment, and share events
- **Database notifications** persisted for offline retrieval
- **Email digest** — daily or hourly summaries for users who are offline when events fire
- `NotificationBell` Livewire component shows unread notification count in the navbar

---

### 📡 Offline Mode

dot.doc keeps working even without an internet connection.

- **Service Worker** caches the app shell and critical assets for offline access
- **IndexedDB** stores in-progress document edits locally
- **Background Sync** — changes made offline are automatically synced to the server when the connection is restored

---

### 🎙️ Voice Typing

Dictate directly into your document using the browser's built-in speech engine.

- Powered by the **Web Speech API** — no third-party dependency required
- Transcribed text is optionally sent to the AI for formatting and clean-up before being inserted

---

### 🔌 Webhooks & Plugins

Extend dot.doc's capabilities with a lightweight plugin system.

- **Custom Slash Commands** — teams can register their own `/commands` in the command palette
- **Webhook Triggers** — configure HTTP webhooks that fire on document save or export events
- Each webhook is stored per-document and executes asynchronously via the job queue

---

### ⚡ Performance & Security

**Performance**

- Redis-backed caching for document content (guests, 5 min TTL) and user permissions (15 min TTL)
- Cache invalidated automatically via model events on update
- Vite chunk splitting and dynamic AI component imports for fast initial load
- Image optimisation via Intervention Image (WebP conversion)
- Paginated document versions and lazy-loaded comments

**Security**

- HTML content sanitised with HTMLPurifier to prevent XSS from editor or AI output
- All AI API inputs validated with length limits and per-user rate limiting
- CSRF protection on all Livewire endpoints
- Team-scoped data access enforced at the policy layer — no cross-team data leakage

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3 / Laravel 12 / Jetstream (Livewire + Teams) |
| Frontend | Tailwind CSS 3, Alpine.js, Livewire 3, Hotwire Turbo |
| Editor | TipTap (Alpine.js wrapper) |
| AI | OpenAI GPT-4o via `openai-php/laravel` |
| Real-time | Laravel Reverb (WebSockets) + Laravel Echo |
| Queue | Laravel Queues + Redis |
| Cache / Session | Redis |
| Database | MySQL / PostgreSQL |
| PDF | barryvdh/laravel-dompdf |
| Word Export | phpoffice/phpword |
| Markdown | league/html-to-markdown, league/commonmark |
| Diff Viewer | jfcherng/php-diff |
| Image | Intervention Image |
| Testing | Pest + PHPUnit |

---

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+ & NPM
- Redis
- MySQL or PostgreSQL

### Installation

```bash
# Clone the repository
git clone https://github.com/sakhileb/Dot.docs.git
cd Dot.docs

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
npm run build
```

---

## Environment Variables

Key variables to configure in `.env`:

```env
# Database
DB_CONNECTION=mysql
DB_DATABASE=dotdoc
DB_USERNAME=root
DB_PASSWORD=

# Redis (cache, sessions, queues)
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Broadcasting (WebSockets)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080

# AI
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o
```

---

## Running the Application

You need four concurrent processes for full functionality:

```bash
# 1. Laravel development server
php artisan serve

# 2. Vite (frontend hot reload)
npm run dev

# 3. Queue worker (AI jobs, notifications, webhooks)
php artisan queue:work

# 4. Reverb WebSocket server (real-time collaboration)
php artisan reverb:start
```

---

## Testing

```bash
php artisan test
```

Tests are written with **Pest** and cover document CRUD, authorization policies, AI service mocking, and real-time broadcasting.

---

## License

dot.doc is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
