# New Path — Codebase Architecture

> This document is the single source of truth for how this codebase is
> structured. Before touching any file, read the relevant section here.
> The **User Dashboard** is the golden reference implementation — when in
> doubt, look at how dashboard does it.

---

## 1. Roles & Folders

```
pages/
  auth/          → login, logout, onboarding, password reset
  user/          → logged-in patient/user pages
  counselor/     → logged-in counselor pages
  admin/         → (out of scope for this guide)

public/assets/js/
  core/          → shared utilities (api.js lives here — Phase 1)
  components/    → reusable UI pieces (sidebar, alert, toast, profile-menu)
  auth/          → scripts for auth pages
  user/          → one JS file per user page
  counselor/     → one JS file per counselor page
```

---

## 2. How Every Page Works (PHP)

Every page follows exactly this 3-file chain. `index.php` is the only entry
point — it glues the three layers together.

```
index.php
  │
  ├── require_once '../common/user.head.php'   ← (1) Auth guard
  ├── require_once './page.controller.php'     ← (2) Data layer
  └── require_once './page.layout.php'         ← (3) HTML layer
```

### Layer 1 — Auth Guard (`user.head.php` / `counselor.head.php`)

- Validates the JWT cookie
- Confirms the role is correct (`user` or `counselor`)
- Sets `$user = ['id', 'name', 'role', ...]` for the rest of the page
- Redirects to login on failure — **the page never loads without a valid user**

### Layer 2 — Controller (`*.controller.php`)

Rules:
- Calls model static methods, assigns results to named `$variables`
- Handles GET flash messages (e.g. `?updateSuccess=1`)
- **Never echoes HTML**
- **Never queries the database directly** — that is the model's job
- No output, just variable assignments

```php
// Good controller pattern
require_once __DIR__ . '/page.model.php';

$userId   = (int) $user['id'];
$sessions = SessionsModel::getUpcoming($userId);
$counselor = SessionsModel::getCounselor($userId);
```

### Layer 3 — Layout (`*.layout.php`)

Rules:
- HTML only — **no DB calls, no business logic**
- Reads the variables set by the controller
- Sets `$activePage`, `$pageTitle`, `$pageStyle`, `$pageScripts` at the top
- Includes shared partials: `user.html.head.php`, `user.sidebar.php`, `user.footer.php`

```php
// Good layout header block (always at the very top of the layout)
$activePage  = 'sessions';
$pageTitle   = 'My Sessions — New Path';
$pageStyle   = ['user/sessions'];
$pageScripts = ['/assets/js/user/sessions.js'];
```

### Model (`*.model.php`)

Rules:
- One class per model file, named `<Feature>Model`
- Only static methods — no instantiation
- Returns plain arrays or scalar values — **no HTML, no redirects**
- All DB access goes through `Database::search()` or `Database::iud()`

```php
class SessionsModel
{
    public static function getUpcoming(int $userId): array { ... }
    public static function getCounselor(int $userId): ?array { ... }
}
```

---

## 3. AJAX Endpoints

AJAX actions (POST/GET that return JSON) live in their own sub-folder:

```
pages/user/sessions/book/hash/index.php   ← AJAX endpoint
pages/user/recovery/task/complete-ajax/index.php
pages/user/community/posts/like/index.php
```

Every AJAX endpoint **must**:
1. Check the request method (`$_SERVER['REQUEST_METHOD']`)
2. Validate input
3. Return a standard JSON envelope using `jsonResponse()`:

```php
// Always this shape — no exceptions:
jsonResponse(['ok' => true,  'data' => [...], 'message' => '']);
jsonResponse(['ok' => false, 'data' => null,  'message' => 'Why it failed']);
```

> `jsonResponse()` is defined in `pages/user/common/response.php` (Phase 2).

---

## 4. How Every JS File Works

One class per file. Named `<Feature>Page`. Instantiated at the bottom.

```js
/**
 * sessions.js — User Sessions page
 */
class SessionsPage {
  constructor() {
    // Query DOM elements once in the constructor
    this.list = document.querySelector('.sessions-list');
    this.#bindEvents();
  }

  // All addEventListener calls go here — nowhere else
  #bindEvents() {
    this.list?.addEventListener('click', (e) => this.#handleClick(e));
  }

  // AJAX actions are private async methods
  async #cancelSession(sessionId) {
    // TODO Phase 1: replace with api.post(...)
    const res = await fetch('/user/sessions/cancel', { ... });
    const data = await res.json();
    if (!data.ok) { this.#showError(data.message); return; }
    // update DOM
  }

  #showError(message) {
    window.NewPathAlert?.show(message, { type: 'error' });
  }
}

// Always exactly this at the bottom — no IIFE, no anonymous functions
document.addEventListener('DOMContentLoaded', () => new SessionsPage());
```

Rules:
- **No global variables** — everything is a class property or method
- **No inline `<script>` tags** in layout files — all JS lives in the `/assets/js/` file
- Private methods use `#prefix`
- DOM queries happen in `constructor()` — not scattered throughout methods

---

## 5. Shared Partials

| File | What it does | Who includes it |
|------|-------------|-----------------|
| `user/common/user.head.php` | Auth guard, sets `$user` | `index.php` (first require) |
| `user/common/user.html.head.php` | `<head>` tag with CSS links | Layout (inside `<html>`) |
| `user/common/user.sidebar.php` | Left nav, reads `$activePage` | Layout (inside `<body>`) |
| `user/common/user.footer.php` | Loads JS scripts from `$pageScripts` | Layout (before `</body>`) |
| `js/components/alert.js` | `NewPathAlert.show(msg, opts)` | Loaded globally via `user.html.head.php` |
| `js/components/sidebar.js` | Sidebar toggle behaviour | Loaded globally via `user.footer.php` |

---

## 6. Page Map (User Role)

| URL | Entry | Controller | Model | JS |
|-----|-------|-----------|-------|----|
| `/user/dashboard` | `pages/user/dashboard/index.php` | `dashboard.controller.php` | `dashboard.model.php` | `user/dashboard.js` |
| `/user/sessions` | `pages/user/sessions/index.php` | `sessions.controller.php` | `sessions.model.php` | `user/sessions.js` |
| `/user/sessions/book` | `pages/user/sessions/book/index.php` | `book.controller.php` | `book.model.php` | *(inline → extract Phase 4)* |
| `/user/recovery` | `pages/user/recovery/index.php` | *(see sub-pages)* | `recovery.model.php` | `user/recovery.js` |
| `/user/community` | `pages/user/community/index.php` | `community.controller.php` | `community.model.php` | `user/community/community.js` |
| `/user/counselors` | `pages/user/counselors/index.php` | `single-counselor.controller.php` | `counselors.model.php` | `user/counselors.js` |
| `/user/profile` | `pages/user/profile/index.php` | *(controller inline)* | `user-profile.model.php` | `auth/user-profile.js` |
| `/user/help` | `pages/user/help/index.php` | *(controller inline)* | — | `user/helpCenter.js` |

---

## 7. Refactoring Phases (Tracking)

| Phase | What | Status |
|-------|------|--------|
| **0** | Golden template — dashboard as reference, this doc | ✅ Done |
| **1** | `public/assets/js/core/api.js` — shared fetch wrapper | ⬜ Todo |
| **2** | Standard JSON envelope + `response.php` helper (~10 endpoints) | ⬜ Todo |
| **3** | Convert all JS files to class pattern | ⬜ Todo |
| **4** | PHP layout audit — split fat files, extract inline scripts | ⬜ Todo |
