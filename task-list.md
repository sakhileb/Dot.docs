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
- [ ] Create new Laravel project (`composer create-project laravel/laravel document-platform`)
- [ ] Install Jetstream with Livewire stack and Teams support (`composer require laravel/jetstream`)
- [ ] Run `php artisan jetstream:install livewire --teams`
- [ ] Install NPM dependencies (`npm install`)

### 1.2 Configure Environment (.env)
- [ ] Set database connection (MySQL/PostgreSQL)
- [ ] Set `SESSION_DRIVER=redis`
- [ ] Set `CACHE_DRIVER=redis`
- [ ] Configure Redis connection (host, port, password)
- [ ] Set `BROADCAST_DRIVER=reverb` (or pusher)
- [ ] Add AI API keys (`OPENAI_API_KEY`, `GEMINI_API_KEY`)

### 1.3 Frontend Tooling
- [ ] Install and configure Tailwind CSS (`npm install -D tailwindcss postcss autoprefixer`)
- [ ] Configure Laravel Mix or Vite (`vite.config.js`)
- [ ] Install required NPM packages:
  ```bash
  npm install alpinejs livewire lodash @hotwired/turbo
  ```
- [ ] Compile assets (`npm run build` or `npm run dev`)

---

## Phase 2: Database & Authentication

### 2.1 Database Migrations
- [ ] Run default migrations (`php artisan migrate`)
- [ ] Extend `users` table (add `avatar`, `bio`, `preferences` JSON)
- [ ] Create `documents` table (id, uuid, title, content (longtext/JSON), owner_id, team_id, version, is_public, created_at, updated_at, deleted_at)
- [ ] Create `document_collaborators` table (id, document_id, user_id, role, last_viewed_at)
- [ ] Create `document_versions` table (id, document_id, content_snapshot, version_number, created_by, created_at)
- [ ] Create `ai_suggestions` table (id, document_id, user_id, suggestion_text, accepted_at, created_at)
- [ ] Create `comments` table (id, document_id, user_id, content, resolved_at, parent_id, created_at)
- [ ] Run migrations

### 2.2 Authentication & Teams
- [ ] Configure Jetstream features (2FA, profile photos, API tokens)
- [ ] Set up team invitations and roles (owner, admin, editor, viewer)
- [ ] Create team-based document policies

---

## Phase 3: Core Document Engine

### 3.1 Document Model & Relationships
- [ ] Create `Document` model with fillable/guarded properties
- [ ] Define relationships (belongs to user/team, has many collaborators, versions, comments)
- [ ] Implement soft deletes
- [ ] Add `DocumentObserver` for version auto-snapshotting

### 3.2 Document CRUD (Livewire)
- [ ] Create `CreateDocument` Livewire component (modal form with title, template selection)
- [ ] Create `EditDocument` Livewire component (main editor interface)
- [ ] Create `DocumentList` Livewire component (dashboard listing with search/filter)
- [ ] Create `DocumentSettings` Livewire component (rename, delete, transfer ownership)
- [ ] Implement document sharing via public links or team/user invites

### 3.3 Rich Text Editor
- [ ] Choose editor: TipTap (Vue) OR Quill with Alpine wrapper
- [ ] Alternative: Integrate `@tinymce/tinymce-laravel` with Livewire
- [ ] Build AlpineJS wrapper for real-time binding to Livewire
- [ ] Implement formatting toolbar (bold, italic, underline, headings, lists, links)
- [ ] Add image embedding (upload to S3/local, store URL in content)
- [ ] Add table support
- [ ] Add undo/redo (via editor native API)

---

## Phase 4: Real-time Collaboration

### 4.1 WebSocket Setup
- [ ] Install Laravel Reverb (`composer require laravel/reverb`)
- [ ] Configure broadcasting in `.env` (`BROADCAST_CONNECTION=reverb`)
- [ ] Set up Presence Channel for each document (`document.{id}`)
- [ ] Install Laravel Echo client-side (`npm install laravel-echo pusher-js`)

### 4.2 Collaborative Editing (CRDT / OT)
- [ ] Implement Yjs or ShareDB integration (Node.js microservice alternative)
- [ ] Simplified approach: Send diffs via Livewire with debouncing
- [ ] Create `DocumentUpdate` broadcast event with user ID, delta content, version
- [ ] Listen on frontend with Echo + Turbo to merge updates
- [ ] Display active cursors with user avatars (store cursor position in Redis)

### 4.3 Presence & Awareness
- [ ] Store active users in Redis sorted set per document
- [ ] Broadcast user join/leave events
- [ ] Show user list with live status (green dot, typing indicator)
- [ ] Implement "Last edited by X at Y time"

---

## Phase 5: Version History & Autosave

### 5.1 Autosave System
- [ ] Debounce content changes (Lodash `_.debounce`, 2-second delay)
- [ ] Save to `documents` table content column via Livewire update
- [ ] Trigger `DocumentVersion` creation every 30 changes or 5 minutes
- [ ] Store version diffs (use `sebdesign/laravel-state-machine` or custom)

### 5.2 Version Browser
- [ ] Create `VersionHistory` Livewire component (sidebar or modal)
- [ ] List all versions with timestamps and user who saved
- [ ] Implement restore functionality (copy content to current)
- [ ] Add compare/diff view (highlight added/removed text)

### 5.3 Export & Import
- [ ] Export to PDF (DomPDF or Browsershot)
- [ ] Export to Word (PHPWord)
- [ ] Export to Markdown / HTML
- [ ] Import from Word / Markdown

---

## Phase 6: AI-Powered Features

### 6.1 AI Service Layer
- [ ] Create `AiService` class (supports OpenAI and Gemini)
- [ ] Implement rate limiting and token usage tracking per user/team
- [ ] Add configuration for AI models (gpt-4, gpt-3.5-turbo, gemini-pro)

### 6.2 AI Writing Assistant
- [ ] **Grammar & Spell Check**: Send text to AI, return corrections with inline suggestions
- [ ] **Summarization**: Generate TL;DR of document
- [ ] **Continue Writing**: AI generates next paragraph based on context
- [ ] **Change Tone**: Formal, casual, persuasive, concise modes
- [ ] **Translate**: Convert content to any language

### 6.3 AI Command Palette (Ctrl+K / Cmd+K)
- [ ] Build AlpineJS modal with AI commands
- [ ] Commands: "/summarize", "/grammar", "/translate es", "/tone formal"
- [ ] Execute command via Livewire, replace selected text with AI output

### 6.4 AI Chat Sidebar
- [ ] Persistent sidebar with chat interface (Ask AI about document)
- [ ] Context-aware answers (AI reads entire document content)
- [ ] Suggestions for improvements, missing sections, facts checking

### 6.5 AI Templates & Smart Formatting
- [ ] Generate document outline from prompt
- [ ] Auto-format meeting notes, project proposals, blog posts
- [ ] Smart tables: "Create a pricing comparison table for 3 products"

---

## Phase 7: Comments & Collaboration

### 7.1 Comment System
- [ ] Create `Comment` model (polymorphic, threaded)
- [ ] Build Livewire comment thread component (resolves to document + text selection)
- [ ] Implement real-time comment notifications (broadcast to collaborators)
- [ ] Add @mentions with user search
- [ ] Resolve/re-open comments

### 7.2 Suggestion Mode (Track Changes)
- [ ] Add toggle: Edit mode vs Suggestion mode
- [ ] In suggestion mode, edits are stored as `ai_suggestions` table
- [ ] Accept/reject buttons for each suggestion (merge or discard)
- [ ] Display suggested changes with diff highlighting

### 7.3 Real-time Notifications
- [ ] Set up database notifications for mentions, comments, shares
- [ ] Broadcast to browser via Echo (toast notifications)
- [ ] Email digest for offline users (daily/hourly)

---

## Phase 8: Performance & Optimization

### 8.1 Caching Strategy
- [ ] Cache document content for guests (5 minutes, Redis)
- [ ] Cache user permissions per document (TTL 15 min)
- [ ] Invalidate cache on update via model events

### 8.2 Lazy Loading & Pagination
- [ ] Paginate document versions (10 per page)
- [ ] Lazy load comments until sidebar opened
- [ ] Implement infinite scroll for document list

### 8.3 Asset Optimization
- [ ] Configure Vite chunk splitting
- [ ] Lazy load AI components (dynamic imports)
- [ ] Optimize images (Intervention Image, WebP conversion)

---

## Phase 9: Security & Permissions

### 9.1 Authorization (Policies)
- [ ] Create `DocumentPolicy` (view, update, delete, manage, share)
- [ ] Team-based access (owner, admin, editor, viewer)
- [ ] Public link access with password/expiration options

### 9.2 Input Sanitization
- [ ] Sanitize HTML content (HTMLPurifier or Laravel's `clean()`)
- [ ] Prevent XSS in comments and AI outputs
- [ ] Validate all AI API inputs (length limits, rate limiting per user)

### 9.3 API Rate Limiting
- [ ] Apply rate limiter for AI endpoints (`20 per minute` per user)
- [ ] Limit document export to `10 per hour`

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
