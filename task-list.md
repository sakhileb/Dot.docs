# Task List: Dot.Docs AI-Powered Document Creation Platform

## Tech Stack
- **Backend**: Laravel with Jetstream (Livewire stack, Teams support)
- **Database**: MySQL/PostgreSQL + Redis for caching/sessions/real-time
- **Frontend**: Tailwind CSS, AlpineJS, Livewire, Turbo, Lodash
- **AI**: OpenAI API / Gemini API (for summarization, grammar, suggestions)
- **Real-time**: Laravel Echo + WebSockets (Laravel Reverb / Pusher)

---

## Phase 1: Environment Setup

### 1.1 Install Laravel & Jetstream
- [x] Create new Laravel project (`composer create-project laravel/laravel document-platform`)
- [x] Install Jetstream with Livewire stack and Teams support (`composer require laravel/jetstream`)
- [x] Run `php artisan jetstream:install livewire --teams`
- [x] Install NPM dependencies (`npm install`)

### 1.2 Configure Environment (.env)
- [x] Set database connection (SQLite)
- [x] Set `SESSION_DRIVER=redis`
- [x] Set `CACHE_DRIVER=redis`
- [x] Configure Redis connection (host, port, password)
- [x] Set `BROADCAST_DRIVER=reverb`
- [x] Add AI API keys (`OPENAI_API_KEY`, `GEMINI_API_KEY`)

### 1.3 Frontend Tooling
- [x] Install and configure Tailwind CSS (`npm install -D tailwindcss postcss autoprefixer`)
- [x] Configure Vite (`vite.config.js`)
- [x] Install required NPM packages:
  ```bash
  npm install alpinejs livewire lodash @hotwired/turbo
  ```
- [x] Compile assets (`npm run build` or `npm run dev`)

---

## Phase 2: Database & Authentication

### 2.1 Database Migrations
- [x] Run default migrations (`php artisan migrate`)
- [x] Extend `users` table (add `bio`, `preferences` JSON)
- [x] Create `documents` table (id, uuid, title, content (longtext/JSON), owner_id, team_id, version, is_public, created_at, updated_at, deleted_at)
- [x] Create `document_collaborators` table (id, document_id, user_id, role, last_viewed_at)
- [x] Create `document_versions` table (id, document_id, content_snapshot, version_number, created_by, created_at)
- [x] Create `ai_suggestions` table (id, document_id, user_id, suggestion_text, accepted_at, created_at)
- [x] Create `comments` table (id, document_id, user_id, content, resolved_at, parent_id, created_at)
- [x] Run migrations

### 2.2 Authentication & Teams
- [x] Configure Jetstream features (2FA, profile photos, API tokens)
- [x] Set up team invitations and roles (owner, admin, editor, viewer)
- [x] Create team-based document policies

---

## Phase 3: Core Document Engine

### 3.1 Document Model & Relationships
- [x] Create `Document` model with fillable/guarded properties
- [x] Define relationships (belongs to user/team, has many collaborators, versions, comments)
- [x] Implement soft deletes
- [x] Add `DocumentObserver` for version auto-snapshotting (UUID generation on create)

### 3.2 Document CRUD (Livewire)
- [x] Create `Documents/Index` Livewire component (grid list with search/filter + create modal)
- [x] Create `Documents/Editor` Livewire component (full editor interface with autosave)
- [x] Create `Documents/ShareManager` Livewire component (public link, collaborator invite/remove)
- [x] Create `Documents/DocumentSettings` Livewire component (rename, public toggle, transfer, delete)
- [x] Implement document sharing via public links or team/user invites

### 3.3 Rich Text Editor
- [x] Chose TipTap editor (Alpine wrapper)
- [x] Build AlpineJS wrapper for real-time binding to Livewire (1.5s debounce autosave)
- [x] Implement formatting toolbar (bold, italic, headings H1–H3, bullet/ordered lists, links)
- [x] Add image embedding (upload to local storage, return URL via DocumentImageController)
- [x] Add table support (TipTap Table extension)
- [x] Add undo/redo (TipTap native history via StarterKit)

---

## Phase 4: Real-time Collaboration

### 4.1 WebSocket Setup
- [x] Install Laravel Reverb (`composer require laravel/reverb`)
- [x] Configure broadcasting in `.env` (`BROADCAST_CONNECTION=reverb`)
- [x] Set up Presence Channel for each document (`document.{id}`)
- [x] Install Laravel Echo client-side (`npm install laravel-echo pusher-js`)

### 4.2 Collaborative Editing (CRDT / OT)
- [x] Simplified approach: Send diffs via Livewire with debouncing
- [x] Create `DocumentUpdated` broadcast event with user ID, content, version
- [x] Listen on frontend with Echo to merge remote updates (skip own edits)
- [x] Display active user avatars (store presence in Redis/Cache via PresenceService)

### 4.3 Presence & Awareness
- [x] Store active users in Cache (Redis) per document via `PresenceService`
- [x] Broadcast `UserJoinedDocument` / `UserLeftDocument` events
- [x] Show active collaborator avatars in editor toolbar (overflow badge for 4+)
- [x] Typing indicator (amber dot) + save status in toolbar
- [x] Implement "Last edited by X at Y time" version display

---

## Phase 5: Version History & Autosave

### 5.1 Autosave System
- [x] Debounce content changes (1.5-second delay via Alpine `setTimeout`)
- [x] Save to `documents` table content column via Livewire `saveContent()`
- [x] Trigger `DocumentVersion` creation on every content change via `DocumentObserver`
- [x] Snapshot records author (`auth()->id()`) and version number

### 5.2 Version Browser
- [x] Create `VersionHistory` Livewire component (full-page split layout)
- [x] List all versions paginated (15/page) with timestamps, author, version badge
- [x] Implement restore functionality — copies snapshot to current, increments version
- [x] Add side-by-side diff view using `jfcherng/php-diff` (word-level highlight)

### 5.3 Export & Import
- [x] Export to PDF (`barryvdh/laravel-dompdf`) — styled blade template
- [x] Export to Word (`phpoffice/phpword`) — `.docx` download
- [x] Export to HTML — self-contained HTML file download
- [x] Export to Markdown (`league/html-to-markdown`) — `.md` download
- [x] Import from Word (`.docx`) — PHPWord parser → HTML content
- [x] Import from Markdown / plain text — CommonMark converter → HTML

---

## Phase 6: AI-Powered Features

### 6.1 AI Service Layer
- [x] Create `AiService` class (supports OpenAI via `openai-php/laravel`)
- [x] Implement rate limiting (20 req/user/hour via `RateLimiter`)
- [x] Add configuration for AI models (`OPENAI_MODEL` env, defaults to `gpt-4o`)

### 6.2 AI Writing Assistant
- [x] **Grammar & Spell Check**: Sends plain text to AI, returns corrections
- [x] **Summarization**: Generates TL;DR (≤150 words) of document
- [x] **Continue Writing**: Generates next paragraph(s) based on existing content
- [x] **Change Tone**: Formal, casual, persuasive, concise rewrite modes
- [x] **Translate**: Converts document to any specified language

### 6.3 AI Command Palette (Ctrl+K / Cmd+K)
- [x] AlpineJS modal triggered by Ctrl+K / Cmd+K global shortcut
- [x] Commands: `/summarize`, `/grammar`, `/continue`, `/tone [style]`, `/translate [lang]`, `/outline`
- [x] Shows command suggestion list with descriptions
- [x] Executes via `AiAssistant` Livewire, shows result in bottom panel with Apply/Dismiss

### 6.4 AI Chat Sidebar
- [x] Floating chat button (bottom-right) opens a 420px panel
- [x] Context-aware: AI receives full document content as system context
- [x] Maintains conversation history (20 turns max) for multi-turn reasoning
- [x] Auto-scrolls to latest message; typing indicator (bouncing dots)

### 6.5 AI Templates & Smart Formatting
- [x] Generate document outline from prompt via `/outline` command
- [x] Auto-format via tone commands (formal, casual, persuasive, concise)
- [x] Free-form AI prompts via command palette (any natural language instruction)

---

## Phase 7: Comments & Collaboration

### 7.1 Comment System
- [x] Create `Comment` model (polymorphic, threaded)
- [x] Build Livewire comment thread component (resolves to document + text selection)
- [x] Implement real-time comment notifications (broadcast to collaborators)
- [x] Add @mentions with user search
- [x] Resolve/re-open comments

### 7.2 Suggestion Mode (Track Changes)
- [x] Add toggle: Edit mode vs Suggestion mode
- [x] In suggestion mode, edits are stored as `ai_suggestions` table
- [x] Accept/reject buttons for each suggestion (merge or discard)
- [x] Display suggested changes with diff highlighting

### 7.3 Real-time Notifications
- [x] Set up database notifications for mentions, comments, shares
- [x] Broadcast to browser via Echo (toast notifications)
- [x] Email digest for offline users (daily/hourly)

---

## Phase 8: Performance & Optimization

### 8.1 Caching Strategy
- [x] Cache document content for guests (5 minutes, Redis)
- [x] Cache user permissions per document (TTL 15 min)
- [x] Invalidate cache on update via model events

### 8.2 Lazy Loading & Pagination
- [x] Paginate document versions (10 per page)
- [x] Lazy load comments until sidebar opened
- [x] Implement infinite scroll for document list

### 8.3 Asset Optimization
- [x] Configure Vite chunk splitting
- [x] Lazy load AI components (dynamic imports)
- [x] Optimize images (Intervention Image, WebP conversion)

---

## Phase 9: Security & Permissions

### 9.1 Authorization (Policies)
- [x] Create `DocumentPolicy` (view, update, delete, manage, share)
- [x] Team-based access (owner, admin, editor, viewer)
- [x] Public link access with password/expiration options

### 9.2 Input Sanitization
- [x] Sanitize HTML content (HTMLPurifier or Laravel's `clean()`)
- [x] Prevent XSS in comments and AI outputs
- [x] Validate all AI API inputs (length limits, rate limiting per user)

### 9.3 API Rate Limiting
- [x] Apply rate limiter for AI endpoints (`20 per minute` per user)
- [x] Limit document export to `10 per hour`

---

## Phase 10: Testing & Deployment

### 10.1 Testing
- [ ] Write Pest tests for document CRUD (authentication, authorization)
- [ ] Test real-time broadcasting (Redis + Reverb locally)
- [ ] Mock AI API responses for feature tests
- [ ] Browser tests with Laravel Dusk (collaborative editing)

### 10.2 Production Deployment
- [ ] Set up queue worker for AI jobs (`php artisan queue:work`)
- [ ] Configure Reverb with SSL and supervisor
- [ ] Set up Horizon (optional) for queue monitoring
- [ ] Deploy to Forge / Vapor / Custom server
- [ ] Configure CDN for assets and images (CloudFront / S3)

### 10.3 Monitoring & Analytics
- [ ] Install Laravel Telescope (local) / Pulse (production)
- [ ] Log AI token usage per document (database table)
- [ ] Set up error tracking (Sentry / Flare)
- [ ] Track active documents and collaboration metrics

---

## Phase 11: Additional Features (Stretch Goals)

### 11.1 Document Templates Gallery
- [ ] Pre-built templates (Resume, Proposal, Meeting Notes, Blog Post)
- [ ] Save current document as template for team

### 11.2 Voice Typing
- [ ] Integrate Web Speech API (frontend transcription)
- [ ] Send transcribed text to AI for formatting

### 11.3 Offline Mode
- [ ] Service Worker + IndexedDB for offline editing
- [ ] Sync when connection restored (background sync)

### 11.4 Add-ons / Plugins System
- [ ] Allow custom slash commands
- [ ] Webhook triggers (on save, on export)

---

## Notes
- Use `hotwired/turbo` to speed up Livewire navigation (Turbo Drive)
- Use Lodash for debouncing autosave and AI calls
- Consider Laravel Scout for full-text search across documents
- For complex collaborative editing, evaluate `LivewireCollaboration` package or integrate `Yjs` via separate Node server
