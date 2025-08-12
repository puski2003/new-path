# NewPath — Product Requirements Document (PRD)

> **Version**: 1.2
> **Date**: 2026-03-31
> **Stack**: PHP (vanilla) · MySQL 8.0 · PayHere (frontend checkout) · Google Meet REST API  
> **Migration**: Java (Servlets/JSP) → PHP  

---

## 1. Product Overview

**NewPath** is an addiction recovery counseling platform that connects users in recovery with licensed counselors. The platform provides tools for session booking, recovery planning, progress tracking, community support, and post-recovery job placement.

### 1.1 Roles

| Role       | Description |
|------------|-------------|
| **User**   | Individuals in recovery. Can book sessions, track progress, join community, follow recovery plans. *(Mostly implemented in PHP)* |
| **Counselor** | Licensed professionals. Manage sessions, create recovery plans, track client progress. *(To be built)* |
| **Admin**  | Platform managers. Oversee users, counselors, finances, content, and system configuration. *(To be built)* |

### 1.2 Current State (PHP)

- ✅ User authentication (login, signup, session-based auth)
- ✅ User onboarding (5-step flow)
- ✅ User dashboard
- ✅ Community posts (CRUD, likes, comments, reports, anonymous posting)
- ✅ Counselor browsing & profile viewing
- ✅ Recovery plans (browse, manage, accept/reject)
- ✅ Session booking UI
- ✅ Help center browsing
- ✅ Post-recovery (job browsing)
- ⬜ Counselor role (only head/sidebar scaffolded)
- ⬜ Admin role (only basic dashboard scaffolded)
- ⬜ PayHere payment integration
- ⬜ Google Meet link generation
- ⬜ Email notification system

---

## 2. User Flows

### 2.1 Session Booking & Payment Flow

This is the **core revenue flow** of the platform.

```
User browses counselors
  → Views counselor profile (bio, specialty, fee, reviews, availability)
  → Selects an available time slot (weekly recurring slots)
  → SLOT LOCKED (first-come-first-served, slot marked unavailable immediately)
  → Proceeds to checkout
  → Pays via PayHere frontend checkout (LKR)
  → On payment success (return URL):
      1. Session record created (status: "scheduled")
      2. Google Meet link generated via REST API
      3. Meet link saved to session record (meeting_link)
      4. Email sent to COUNSELOR with:
         - Patient name, session datetime, session type
         - Google Meet link (clickable)
      5. Email sent to USER with:
         - Confirmation, session datetime
         - Google Meet link
      6. User redirected to payment success page
  → On payment failure/cancel (cancel URL):
      - Slot released back to available
      - User shown error, can retry
      - No session created
```

**Slot Locking:**
- When a user selects a slot and proceeds to checkout, the slot is **immediately locked** (marked unavailable)
- If another user tries to book the same slot → shown "slot no longer available"
- If payment fails or user cancels → slot **released** back to available
- First-come-first-served — no queuing

**PayHere Integration Details:**
- **Frontend-only checkout** — use PayHere's JavaScript checkout form (no server-side merchant API)
- Currency: **LKR**
- Mode: **Sandbox** (for development), switchable to production
- Flow: Generate hash on server → render PayHere checkout button → user pays → redirect to return/cancel URL
- On return URL: verify payment status via the return parameters, create session record
- Refunds: Handled **manually by admin** from PayHere merchant dashboard
- Replace all Stripe references in DB schema (`stripe_payment_intent_id` → `payhere_order_id`, etc.)
- **No server-side notify/callback URL needed** — payment verification happens on the return redirect

**Google Meet Integration Details:**
- Use **Google Meet REST API** directly (not Calendar API)
- Service account authentication
- Generate a unique meeting link per session
- Store link in `sessions.meeting_link`

### 2.2 Session Lifecycle

```
scheduled → (session time arrives) → in_progress → completed
                                   → no_show (if either party doesn't join)
scheduled → cancelled (by user or counselor)
```

**Session Duration & Extension:**
- Counselors set their default session duration (30, 60, or 90 min) and corresponding fee
- During a live session, the **user can request to extend** the session
- Extension adds 30-minute blocks at a pro-rated price
- Extension payment processed via PayHere before the extra time begins
- If no extension payment, session ends at scheduled time

**Rescheduling:**
- Counselor can request a reschedule if unable to attend
- User receives notification + email with reschedule request
- User can accept (new time) or request refund instead
- If refund: admin processes manually via PayHere dashboard

**Post-Session:**
- Counselor can write `session_notes` (private to counselor only, NOT visible to user or admin)
- Counselor can write `counselor_private_notes` (also counselor-only)
- User can rate the session (1-5 stars) and leave a review
- Counselor's average rating updates automatically

**Platform Commission:**
- The platform takes a percentage cut from each session payment
- Commission rate configurable via `system_settings` table
- Counselor earnings = session fee − platform commission
- Commission tracked in `counselor_payouts` table

### 2.3 Onboarding Risk Scoring

At the end of the 5-step onboarding, the system calculates a **risk score** from user responses to determine whether to recommend a pre-built recovery plan or direct counselor booking. This is a recommendation only — not a medical diagnosis. A disclaimer is shown to the user.

**Scoring Model:**

| Field | Answer | Points |
|---|---|---|
| Primary substance | Alcohol / Cannabis | 1 |
| | Prescription drugs / Stimulants | 2 |
| | Heroin / Methamphetamine / Opioids | 3 |
| Frequency of use | Occasionally (monthly) | 1 |
| | Weekly | 2 |
| | Daily / Multiple times daily | 3 |
| Last used | More than 1 month ago | 1 |
| | Within last 2 weeks | 2 |
| | Within last 3 days | 3 |
| Previous quit attempts | 0–1 attempts | 1 |
| | 2–3 attempts | 2 |
| | 4+ failed attempts | 3 |
| Recovery motivation | Just exploring | 1 |
| | Motivated but struggling | 2 |
| | Desperate / affecting daily life | 3 |

**Decision Logic:**
```
Score 5–7  (LOW)      → Highlight: "Start with a pre-built plan"
                         Option available: Book a counselor
Score 8–11 (MODERATE) → Both options shown equally
Score 12+  (HIGH)     → Highlight: "We recommend booking a counselor first"
                         Option available: Browse pre-built plans
```

**Implementation notes:**
- Score stored in `user_profiles.risk_score` (INT)
- Score recalculated if user updates substance/frequency info in profile
- Counselors can see the risk score on the client profile page
- Disclaimer shown on result screen: *"This recommendation is based on your answers and is not a medical diagnosis. You are free to choose either option."*

---

### 2.4 Recovery Plan Flow

```
Counselor creates plan for a user they have a booked session with
  → Plan created with status "draft"
  → Counselor adds goals (short-term/long-term) and tasks (organised by phase)
  → Counselor publishes plan (status: "active", assigned_status: "pending")
  → User receives notification + email
  → User can:
      - ACCEPT the plan → assigned_status: "accepted", user starts working on tasks
      - REJECT the plan → assigned_status: "rejected", counselor notified
  → User works through tasks phase by phase
      - Phase N tasks must all be completed before Phase N+1 unlocks
      - Each task has an optional reflection field (private to user by default)
      - User can REQUEST a task change → counselor is notified → counselor edits task
  → Completing all tasks in all phases → plan status: "completed"
  → Counselor reviews plan progress on client profile at each session
```

**Clinical Basis (research-backed):**
- Phased structure (Early/Middle/Late recovery) is the SAMHSA and NCBI clinical standard
- Counselor-creates / patient-completes is the model used by Workit Health
- CBT homework meta-analysis: homework with therapist review predicts better outcomes than homework without review (PMC: CBT Mobile App Homework Compliance)
- Non-completion rates spike to 40%+ in automated systems with no human follow-up

**Key Rules:**
- Counselors can ONLY create plans for users who have **booked at least one session** with them
- Counselors can create plans **before or after** the session
- Users can also create **self-directed plans** (`plan_type: "self"`) independently
- Plans can be paused/resumed by the user
- Users **cannot directly edit** task content — they submit a change request which the counselor must approve

**Task Completion Model:**

A task is not just a checkbox. When a user marks a task complete, they are shown an optional reflection prompt:

```
✓  Task: "Attend one support group meeting this week"

How did it go? (optional — only you can see this)
[________________________________________________]

[Mark Complete]
```

- Reflection response is stored in `recovery_tasks.user_reflection` (TEXT, nullable)
- Reflection is private to the user — counselors cannot read it
- Its purpose is to slow the user down and encourage genuine engagement rather than mindless ticking
- Research basis: Sober Grid data showed a 14:1 discrepancy between self-reported and actual relapses — simple binary checkboxes enable dishonest completion

**Counselor Visibility on Client Profile:**

Counselors see aggregate plan progress — not journal entries, urge logs, or check-in history:

```
┌─────────────────────────────────────────────────┐
│ Kasun's Plan: "Alcohol Recovery - Phase 1"      │
│ Progress:  ████████░░  80%  (8 of 10 tasks)     │
│ Current Phase: Phase 2 (unlocked 3 days ago)    │
│ Last task completed: 2 days ago                 │
│ Overdue tasks: 1                                │
│ [View Task List]                                │
└─────────────────────────────────────────────────┘
```

This complies with the data privacy rules in §9.1 — counselors see clinical progress signals only.

**Standard Clinical Phases (reference):**

| Phase | Typical Duration | Focus |
|---|---|---|
| Early (Stabilisation) | Weeks 1–8 | Establishing sobriety, building routine, crisis management |
| Middle (Active Treatment) | Months 2–6 | CBT skills, trigger identification, coping strategies |
| Late (Maintenance) | Month 6+ | Relapse prevention, social reintegration, job support |

### 2.5 Post-Session Follow-Up Thread

After a session is marked **completed**, a time-limited async message thread opens between the user and the counselor. This replaces the need for free-form, open-ended chat (which would undercut counselor income and remove clinical structure).

**Flow:**

```
Session marked "completed"
  → session_messages thread auto-created (linked to session_id)
  → Both user and counselor notified: "Your session is complete.
     You have 7 days to send follow-up messages."

Within 7-day window (up to 5 messages total):
  → Either party can send a text message
  → Recipient sees in-app notification + email

After 7 days OR 5 messages reached:
  → Thread locked (read-only)
  → UI shows: "Follow-up window closed."
  → CTA shown to user: [Book Another Session with [Counselor Name]]
```

**Rules:**
- Thread is per-session — one completed session = one thread
- Max **5 messages** across both parties combined
- Thread expires **7 days** after session completion
- No attachments — text only
- Both parties can see the full thread history (read-only after lock)
- Counselor's follow-up messages are **not** session notes — they are visible to the user
- This is **not** a general chat — it is scoped to session follow-up only

**Why these limits:**
- 7-day window: Matches clinical evidence that post-session questions peak in 24–72 hours
- 5-message cap: Prevents counselors from being expected to provide free ongoing therapy; keeps the boundary clear
- Hard expiry + "Book Again" CTA: Converts follow-up engagement into revenue, not into free support

**UI Wireframe (User Side):**

```
┌──────────────────────────────────────────────────────────┐
│ Follow-up: Session with Dr. Nimal Perera (Mar 28, 2026)  │
│ 3 of 5 messages used  ·  Closes in 4 days               │
├──────────────────────────────────────────────────────────┤
│ Dr. Nimal  [Mar 28, 10:02 AM]                            │
│ "Great session today. Keep working on the breathing       │
│ exercise before bed."                                     │
│                                                          │
│ You  [Mar 29, 8:45 AM]                                   │
│ "Thanks. I had a rough moment yesterday — is that        │
│ normal at this stage?"                                    │
│                                                          │
│ Dr. Nimal  [Mar 29, 9:15 AM]                             │
│ "Completely normal. It's the brain adjusting. Reach out  │
│ the support group if it gets intense."                    │
├──────────────────────────────────────────────────────────┤
│ [Type your message...]                          [Send]   │
│ 2 messages remaining                                     │
└──────────────────────────────────────────────────────────┘
```

**After thread expires:**

```
┌──────────────────────────────────────────────────────────┐
│ Follow-up window has closed.                             │
│ Want to continue working with Dr. Nimal Perera?          │
│                                                          │
│        [Book Another Session →]                          │
└──────────────────────────────────────────────────────────┘
```

---

### 2.6 Counselor Page Restructure (User Side)

The current counselors page is a single directory/marketplace. It needs to be restructured into **two tabs** to give users a clear view of their existing counselor relationships alongside the ability to find new ones.

**Tab 1 — My Counselors:**

Shows counselors the user has had **at least one completed session** with.

```
┌─────────────────────────────────────────────────────────┐
│  [My Counselors]  |  Find a Counselor                   │
├─────────────────────────────────────────────────────────┤
│  ┌──────────────────────────────────────────────────┐   │
│  │  Dr. Nimal Perera  ·  Addiction Specialist       │   │
│  │  Last session: March 28, 2026                    │   │
│  │  Sessions together: 4                            │   │
│  │                                                  │   │
│  │  [Send Follow-up Message]   [Book Again]         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                         │
│  ┌──────────────────────────────────────────────────┐   │
│  │  Dr. Amali Silva  ·  CBT Therapist               │   │
│  │  Last session: February 15, 2026                 │   │
│  │  Sessions together: 2                            │   │
│  │                                                  │   │
│  │  [Send Follow-up Message]   [Book Again]         │   │
│  └──────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

- "Send Follow-up Message" button is **only active** if there is an open follow-up thread (i.e., a completed session within the last 7 days with remaining messages)
- If no open thread: button is greyed out with tooltip "No active follow-up window"
- "Book Again" goes directly to that counselor's booking page

**Tab 2 — Find a Counselor:**

Existing counselor directory/marketplace (search, filter by specialty, fee, language, rating). No changes to existing logic.

**Logic for "My Counselors" tab:**
- Query: users with at least one session (`status = 'completed'`) with each counselor
- Ordered by most recent session date (most recent first)
- If user has no completed sessions → Tab 1 shows empty state: *"Your counselors will appear here after your first session."*

---

### 2.7 Counselor Application Flow

```
Visitor fills out counselor application form:
  - Full name, email, phone
  - Title, specialty, bio
  - Experience years, education, certifications
  - Languages spoken, consultation fee
  - Availability schedule
  - Upload supporting documents (certificates, licenses)
  → Application saved (status: "pending")
  → Admin receives notification

Admin reviews application:
  → Views submitted info + uploaded documents
  → Can add admin_notes
  → Approves or rejects:
      - APPROVED: System auto-creates user account (role: "counselor") + counselor profile
        → Applicant receives email with login credentials
      - REJECTED: Applicant receives email with rejection reason
```

### 2.8 Community Flow

```
User creates a post (general, success_story, question, support_request, resource)
  → Can attach an image
  → Can post anonymously
  → Other users can: like, comment, share, save, report
  → Reported posts → admin review queue
  → Admin can: hide/delete post OR ban the reporting user
```

### 2.9 User Progress Tracking

```
Daily check-in: mood, energy, sleep quality, stress level, notes
Urge logging: intensity, trigger category, coping strategy used, outcome
Sobriety tracker: days_sober counter, milestones
Journal entries: categorized (gratitude, progress, challenge, reflection)
Achievements: badge system based on days/milestones reached
```

### 2.10 Email Notifications (All)

| Event | Recipient | Content |
|-------|-----------|---------|
| Session booked + paid | User + Counselor | Confirmation + Google Meet link |
| Payment receipt | User | Transaction details, amount in LKR |
| Session reminder (24h before) | User + Counselor | Meeting details + Meet link |
| Session cancelled | User + Counselor | Cancellation reason |
| Reschedule requested | User | New proposed time, accept/refund options |
| Recovery plan assigned | User | Plan title, counselor name, accept/reject CTA |
| Recovery plan accepted/rejected | Counselor | User's response |
| Post reported | Admin | Report details |
| Counselor application submitted | Admin | Application summary |
| Counselor application approved | Applicant | Login credentials |
| Counselor application rejected | Applicant | Rejection reason |
| Post-session follow-up message received | User or Counselor | Message preview + link to thread |
| Follow-up thread expiring (24h notice) | User + Counselor | "Thread closes tomorrow" + Book Again CTA |
| Community post flagged/removed | Post author | Reason for removal |
| Achievement earned | User | Badge details |

---

## 3. Counselor Role — Feature Specification

### 3.1 Counselor Dashboard

| Widget | Description |
|--------|-------------|
| Today's sessions | List of sessions for today with Meet links, patient names |
| Upcoming sessions | Next 7 days of scheduled sessions |
| Recent clients | Quick access to recent client profiles |
| Stats cards | Total clients, total sessions, rating, pending plans |
| Session calendar | Monthly view with color-coded session statuses |
| Notifications | Recent alerts (new bookings, plan responses, etc.) |

### 3.2 Sessions Management

- **View all sessions**: filterable by status (scheduled, completed, cancelled, no_show)
- **Session detail**: patient info, datetime, Meet link (clickable to join), session type
- **Post-session actions**:
  - Mark as completed
  - Write session notes (private, counselor-only)
  - Write private notes
- **Request reschedule**: select new time from own availability, sends notification to user
- **Cancel session**: with cancellation reason, triggers refund request to admin

### 3.3 Client Management

- **Client list**: all users who have booked sessions with this counselor
- **Client profile view**:
  - Basic demographics only (name, age, gender)
  - Session history with this counselor
  - Recovery plans assigned by this counselor
  - **NOT visible (GDPR/data privacy compliance)**:
    - ❌ Sobriety days / progress tracker
    - ❌ Daily check-in history (mood, energy, sleep, stress)
    - ❌ Urge logs
    - ❌ Journal entries
    - ❌ Community posts
    - ❌ Other counselor's data or plans
  - **Rationale**: Under data protection principles (GDPR/Sri Lanka's Personal Data Protection Act), sensitive health and behavioral data should not be shared with third parties (including counselors) without explicit, informed user consent. Recovery tracking data is self-reported for the user's own benefit and should remain private unless the user explicitly chooses to share specific data with their counselor.

### 3.4 Recovery Plans

- **My plans**: list of all plans created by this counselor
- **Create plan**: only for users who have at least one booking with this counselor
  - Title, description, category
  - Add goals (short-term/long-term with target days)
  - Add tasks organized by phases (1, 2, 3...)
  - Each task: title, description, type, priority, due date, recurring option
  - Save as draft or publish immediately
- **View plan**: see plan + aggregate user progress on tasks (see below)
- **Edit task content**: counselors can edit a task at any time; edits notify the user
- **Handle change requests**: if a user submits a "Request Change" on a task, the counselor sees it in notifications and can edit the task or dismiss the request
- **Delete plan**: soft-delete with confirmation

**Counselor view of client plan progress (on Client Profile):**

Counselors see aggregate progress signals only — no journal entries, urge logs, or check-in history (see §9.1 for data privacy rationale):

```
┌─────────────────────────────────────────────────┐
│ Kasun's Plan: "Alcohol Recovery - Phase 1"      │
│ Progress:  ████████░░  80%  (8 of 10 tasks)     │
│ Current Phase: Phase 2 (unlocked 3 days ago)    │
│ Last task completed: 2 days ago                 │
│ Overdue tasks: 1                                │
│ [View Task List]                                │
└─────────────────────────────────────────────────┘
```

"View Task List" shows each task title, status (complete / incomplete / overdue), and completion date. It does **not** show user reflection text (reflection is private to the user).

### 3.5 Counselor Profile Management

- **Edit profile**: title, specialty, bio, experience, education, certifications, languages, fee
- **Manage availability**: set weekly recurring slots
  - Day of week + start time + end time
  - Multiple slots per day allowed
  - Toggle slots on/off
- **View own reviews**: ratings and review text from users

---

## 4. Admin Role — Feature Specification (Priority Phase)

### 4.1 Admin Dashboard

| Widget | Description |
|--------|-------------|
| Platform stats | Total users, counselors, active sessions, revenue |
| Recent activity | Latest registrations, bookings, reports |
| Pending items | Counselor applications awaiting review, reported posts |
| Revenue chart | Monthly revenue trends |
| User growth chart | Registration trends |

### 4.2 User Management

- **User list**: searchable, filterable by role, status, registration date
- **User detail**: profile info, session history, recovery plans, community activity
- **Actions**: activate/deactivate user, ban user, reset password
- **Ban user**: removes from community, prevents login, with reason

### 4.3 Counselor Application Management

- **Application queue**: list of pending applications
- **Review view**: full application details + uploaded documents (viewable/downloadable)
- **Actions**:
  - Approve: auto-creates counselor user + sends credentials via email
  - Reject: add reason + sends rejection email
- **Application history**: past approved/rejected applications

### 4.4 Counselor Management

- **Counselor list**: all active counselors with stats (rating, sessions, clients)
- **Counselor detail**: profile, session history, client list, revenue generated
- **Actions**: verify/unverify counselor, deactivate account

### 4.5 Content Moderation

- **Reported posts queue**: posts flagged by users
- **Report detail**: post content, reporter info, reason, description
- **Actions**:
  - Dismiss report (no action)
  - Hide/delete the post (author notified via email)
  - Ban the post author (full platform ban)
- **Report history**: past moderation actions with audit trail

### 4.6 Finances

- **Transaction list**: all payments, filterable by status, date, counselor
- **Transaction detail**: user, counselor, amount (LKR), PayHere order ID, status
- **Refund disputes**: list of refund requests with issue type and description
- **Revenue dashboard**: total revenue, counselor payouts, platform commission
- **Note**: Actual refunds processed manually in PayHere merchant dashboard

### 4.7 Help Center Management

- **CRUD help centers**: name, organization, type, category, contact info, address, availability
- **Toggle active/inactive**

### 4.8 Job Posts Management

- **CRUD job posts**: title, company, description, requirements, location, type, category, salary
- **Toggle active/inactive**
- **Only admins** can create job posts

### 4.9 Support Groups (Virtual Group Chats)

- **Create support group**: name, description, category, meeting schedule, max members 
- **Only admins** can create groups
- **Users join freely** — no approval needed
- **Dual format**:
  1. **Real-time text chat**: Built-in group messaging within the platform
     - Persistent message history
     - Members can send text messages
     - Admin/moderator can pin messages, delete inappropriate ones
  2. **Scheduled Google Meet sessions**: Admin can arrange group video calls
     - Admin sets meeting link + schedule
     - Members see upcoming group meetings on their dashboard
     - Meeting link visible to all group members
- **Admin can**: deactivate groups, remove members, moderate chat

**Real-Time Chat Technical Approach:**
- Use **AJAX polling** (every 3-5 seconds) for simplicity — no WebSocket server needed
- Messages stored in a `support_group_messages` table
- Message schema: `message_id`, `group_id`, `user_id`, `content`, `created_at`
- Load last 50 messages on page load, poll for new ones
- Future: upgrade to WebSocket for true real-time

### 4.10 Admin Permissions

- **All admins have full access** — no permission levels or restrictions
- The `is_super_admin` flag in the DB is reserved for future use
- Any admin can manage users, counselors, content, finances, and settings

### 4.11 System Settings

- **Platform settings**: site name, maintenance mode, default values, **platform commission rate**
- **Configurable via `system_settings` table** (key-value pairs)

---

## 5. Database Schema Changes

### 5.1 PayHere Migration

```sql
-- transactions table
ALTER TABLE transactions 
  DROP COLUMN stripe_payment_intent_id,
  ADD COLUMN payhere_order_id VARCHAR(255) DEFAULT NULL,
  ADD COLUMN payhere_payment_id VARCHAR(255) DEFAULT NULL,
  ADD COLUMN payhere_status_code VARCHAR(10) DEFAULT NULL,
  MODIFY COLUMN currency VARCHAR(3) DEFAULT 'LKR';

-- counselor_payouts table
ALTER TABLE counselor_payouts
  DROP COLUMN stripe_payout_id,
  ADD COLUMN payhere_reference VARCHAR(255) DEFAULT NULL,
  ADD COLUMN platform_commission DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN commission_rate DECIMAL(5,2) DEFAULT 0.00,
  MODIFY COLUMN currency VARCHAR(3) DEFAULT 'LKR';

-- payment_methods table (simplified for PayHere frontend)
ALTER TABLE payment_methods
  DROP COLUMN stripe_payment_method_id,
  MODIFY COLUMN method_type ENUM('card','bank_transfer','payhere') DEFAULT 'payhere';

-- sessions table — support flexible duration
ALTER TABLE sessions
  ADD COLUMN extended_minutes INT DEFAULT 0,
  ADD COLUMN extension_fee DECIMAL(10,2) DEFAULT 0.00;
```

### 5.2 Google Meet

No schema changes needed — `sessions.meeting_link` already exists.

### 5.3 Document Uploads for Counselor Applications

```sql
-- New table for application documents
CREATE TABLE IF NOT EXISTS counselor_application_documents (
  document_id INT NOT NULL AUTO_INCREMENT,
  application_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_type VARCHAR(50) DEFAULT NULL,
  file_size INT DEFAULT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (document_id),
  CONSTRAINT fk_doc_application FOREIGN KEY (application_id) 
    REFERENCES counselor_applications(application_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5.4 Support Group Chat Messages

```sql
CREATE TABLE IF NOT EXISTS support_group_messages (
  message_id INT NOT NULL AUTO_INCREMENT,
  group_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  is_pinned TINYINT(1) DEFAULT 0,
  is_deleted TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id),
  KEY idx_group (group_id),
  KEY idx_created (created_at),
  CONSTRAINT fk_msg_group FOREIGN KEY (group_id) REFERENCES support_groups(group_id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5.5 Slot Locking (Booking Holds)

```sql
CREATE TABLE IF NOT EXISTS booking_holds (
  hold_id INT NOT NULL AUTO_INCREMENT,
  counselor_id INT NOT NULL,
  user_id INT NOT NULL,
  slot_datetime DATETIME NOT NULL,
  duration_minutes INT DEFAULT 60,
  status ENUM('held','confirmed','released') DEFAULT 'held',
  held_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NOT NULL,
  PRIMARY KEY (hold_id),
  KEY idx_counselor_slot (counselor_id, slot_datetime),
  CONSTRAINT fk_hold_counselor FOREIGN KEY (counselor_id) REFERENCES counselors(counselor_id) ON DELETE CASCADE,
  CONSTRAINT fk_hold_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> **Note**: Holds expire after 15 minutes. A cron job or check-on-read pattern releases expired holds.

### 5.6 Risk Score & Task Reflection Columns

```sql
-- Store onboarding risk score on user profile
ALTER TABLE user_profiles
  ADD COLUMN risk_score INT DEFAULT NULL COMMENT 'Onboarding risk score (5–15). NULL = not yet calculated.';

-- Store optional user reflection when marking a recovery task complete
ALTER TABLE recovery_tasks
  ADD COLUMN user_reflection TEXT DEFAULT NULL COMMENT 'Private user reflection on task completion. Visible to user only.';
```

### 5.7 Post-Session Follow-Up Messages

```sql
CREATE TABLE IF NOT EXISTS session_messages (
  message_id    INT NOT NULL AUTO_INCREMENT,
  session_id    INT NOT NULL,
  sender_id     INT NOT NULL,           -- users.user_id
  sender_role   ENUM('user','counselor') NOT NULL,
  content       TEXT NOT NULL,
  sent_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_read       TINYINT(1) DEFAULT 0,
  PRIMARY KEY (message_id),
  KEY idx_session (session_id),
  KEY idx_sender (sender_id),
  CONSTRAINT fk_smsg_session FOREIGN KEY (session_id)
    REFERENCES sessions(session_id) ON DELETE CASCADE,
  CONSTRAINT fk_smsg_sender FOREIGN KEY (sender_id)
    REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business rules enforced at application layer (not DB constraints):**
- Max 5 messages per session (enforced in PHP before INSERT)
- Thread expires 7 days after `sessions.completed_at` (checked on read + AJAX poll)
- Thread opens only when `sessions.status = 'completed'`

---

## 6. Technical Architecture

### 6.1 PHP Project Structure

```
new-path/
├── config/
│   ├── database.php          # DB connection (MySQL)
│   └── env.php               # Environment variables
├── core/
│   ├── Auth.php              # Session-based authentication
│   ├── Request.php           # Request helper
│   └── Response.php          # Response helper
├── pages/
│   ├── auth/                 # Login, signup, onboarding, profile
│   ├── user/                 # ✅ User pages (mostly done)
│   ├── counselor/            # ⬜ Counselor pages (to build)
│   │   ├── common/           # Shared head, sidebar
│   │   ├── dashboard/        # Dashboard with stats + calendar
│   │   ├── sessions/         # Session management
│   │   ├── clients/          # Client list + profiles
│   │   ├── recovery-plans/   # Create, view, manage plans
│   │   └── profile/          # Edit counselor profile + availability
│   └── admin/                # ⬜ Admin pages (to build)
│       ├── common/           # Shared head, sidebar
│       ├── dashboard/        # Overview dashboard
│       ├── users/            # User management
│       ├── counselor-apps/   # Application review
│       ├── counselors/       # Counselor management
│       ├── content/          # Content moderation
│       ├── finances/         # Transaction management
│       ├── help-centers/     # Help center CRUD
│       ├── job-posts/        # Job post CRUD
│       ├── support-groups/   # Group management
│       └── settings/         # System settings
├── public/
│   ├── css/
│   ├── js/
│   └── uploads/              # Local file storage (path in .env)
└── .env                      # Environment config
```

### 6.2 Page Pattern (MVC-ish)

Each page folder follows this pattern (already established):
```
feature/
├── index.php               # Entry point, routes to controller
├── feature.controller.php  # Handles request, calls model, passes to layout
├── feature.model.php       # Database queries
└── feature.layout.php      # HTML/PHP template
```

### 6.3 Environment Variables

```env
# Existing
DB_HOST=127.0.0.1
DB_NAME=new_path_2
DB_USER=root
DB_PASS=****

# New — PayHere
PAYHERE_MERCHANT_ID=
PAYHERE_MERCHANT_SECRET=
PAYHERE_SANDBOX=true
PAYHERE_NOTIFY_URL=
PAYHERE_RETURN_URL=
PAYHERE_CANCEL_URL=

# New — Google Meet
GOOGLE_SERVICE_ACCOUNT_KEY_PATH=
GOOGLE_MEET_SPACE_TYPE=

# New — Email (SMTP)
SMTP_HOST=
SMTP_PORT=
SMTP_USER=
SMTP_PASS=
SMTP_FROM_NAME=NewPath
SMTP_FROM_EMAIL=

# New — Uploads
UPLOAD_BASE_PATH=./public/uploads
UPLOAD_MAX_SIZE_MB=10
```

### 6.4 Authentication & Authorization

- **Session-based auth** (PHP native sessions)
- Role stored in `$_SESSION['role']` after login
- Separate login pages: `/auth/login/user`, `/auth/login/counselor`, `/auth/login/admin`
- Auth middleware checks role per route:
  - `/pages/counselor/*` → requires `role === 'counselor'`
  - `/pages/admin/*` → requires `role === 'admin'`
  - `/pages/user/*` → requires `role === 'user'`

---

## 7. Implementation Priority

### Phase 1 — Counselor Core (Must Have)
1. Counselor login
2. Counselor dashboard (today's sessions, stats, calendar)
3. Session management (view, complete, notes, reschedule, cancel)
4. Client list + client profile view
5. Recovery plan CRUD (create for booked users, goals, phased tasks)
6. Counselor profile management + availability slots

### Phase 2 — Payment & Meetings (Must Have)
1. PayHere checkout integration (sandbox)
2. PayHere callback/notify handler (verify hash, update transaction)
3. Google Meet link generation on successful payment
4. Payment success/failure pages
5. Transaction record creation

### Phase 3 — Admin Core (Must Have)
1. Admin login
2. Admin dashboard (stats, pending items, charts)
3. User management (list, detail, activate/deactivate/ban)
4. Counselor application review (view, approve/reject, auto-create account)
5. Counselor management (list, verify, deactivate)
6. Content moderation (reported posts, hide/delete/ban)

### Phase 4 — Email & Notifications
1. SMTP email service (PHPMailer)
2. Session booking confirmation emails
3. Payment receipt emails
4. Session reminder emails (cron job, 24h before)
5. Recovery plan assignment/response emails
6. Counselor application result emails
7. In-app notification system (DB-backed, shown in dashboards)

### Phase 5 — Admin Extended + Post-Recovery
1. Finance dashboard + transaction management
2. Help center CRUD
3. Job posts CRUD
4. Support groups (create, member management, virtual group chats)
5. System settings
6. Audit logging

---

## 8. User Flow Diagrams

### 8.1 Session Booking → Meeting

```
┌──────────┐     ┌──────────────┐     ┌──────────┐     ┌──────────────┐
│  Browse   │────▶│  View Profile │────▶│  Select  │────▶│   Checkout   │
│ Counselors│     │ + Availability│     │ Time Slot│     │  (PayHere)   │
└──────────┘     └──────────────┘     └──────────┘     └──────┬───────┘
                                                              │
                                              ┌───────────────┼───────────────┐
                                              ▼               │               ▼
                                        ┌──────────┐          │         ┌──────────┐
                                        │ Payment  │          │         │ Payment  │
                                        │ SUCCESS  │          │         │ FAILED   │
                                        └────┬─────┘          │         └──────────┘
                                             │                │
                                     ┌───────┼────────┐       │
                                     ▼       ▼        ▼       │
                               ┌─────────┐ ┌──────┐ ┌──────┐  │
                               │ Create  │ │Google│ │Email │  │
                               │ Session │ │ Meet │ │ Both │  │
                               │ Record  │ │ Link │ │Parties│ │
                               └─────────┘ └──────┘ └──────┘  │
```

### 8.2 Session Day

```
┌───────────────┐     ┌───────────────┐     ┌───────────────┐
│ 24h Reminder  │────▶│ Session Time  │────▶│  Join via      │
│ Email Sent    │     │  Arrives      │     │  Google Meet   │
└───────────────┘     └───────────────┘     └───────┬───────┘
                                                     │
                                          ┌──────────┼──────────┐
                                          ▼          │          ▼
                                    ┌──────────┐     │    ┌──────────┐
                                    │ Session  │     │    │  No Show │
                                    │ Completed│     │    │  Marked  │
                                    └────┬─────┘     │    └──────────┘
                                         │           │
                                 ┌───────┼───────┐   │
                                 ▼       ▼       ▼   │
                           ┌─────────┐ ┌──────┐ ┌────────────┐
                           │Counselor│ │ User │ │ Counselor  │
                           │ Writes  │ │Rates │ │Creates Plan│
                           │ Notes   │ │Session││ (Optional) │
                           └─────────┘ └──────┘ └────────────┘
```

### 8.3 Recovery Plan Lifecycle

```
┌───────────────┐     ┌───────────────┐     ┌───────────────┐
│  Counselor    │────▶│   Counselor   │────▶│   Plan Sent   │
│ Creates Plan  │     │ Adds Goals +  │     │   to User     │
│  (Draft)      │     │ Phased Tasks  │     │  (Pending)    │
└───────────────┘     └───────────────┘     └───────┬───────┘
                                                     │
                                          ┌──────────┼──────────┐
                                          ▼                     ▼
                                    ┌──────────┐          ┌──────────┐
                                    │  User    │          │   User   │
                                    │ ACCEPTS  │          │ REJECTS  │
                                    └────┬─────┘          └──────────┘
                                         │
                                         ▼
                                    ┌──────────┐
                                    │  User    │
                                    │ Completes│
                                    │ Tasks by │
                                    │  Phase   │
                                    └────┬─────┘
                                         │
                                         ▼
                                    ┌──────────┐
                                    │  Plan    │
                                    │ Completed│
                                    └──────────┘
```

---

## 9. Non-Functional Requirements

| Requirement | Specification |
|-------------|---------------|
| **Response Time** | Pages load within 2 seconds |
| **Security** | Password hashing (bcrypt), SQL injection prevention (prepared statements), XSS prevention, CSRF tokens |
| **File Uploads** | Max 10MB per file, allowed: jpg, png, pdf, doc. Stored locally, path configurable via `.env` |
| **Browser Support** | Chrome, Firefox, Safari, Edge (latest 2 versions) |
| **Mobile** | Responsive design (existing CSS handles this) |
| **Session Timeout** | 30 minutes of inactivity |
| **Audit Trail** | All admin actions logged to `audit_logs` table |

---

## 9.1 Data Privacy & Protection (GDPR / Sri Lanka PDPA Compliance)

Since this platform handles **sensitive health and behavioral data**, the following privacy rules apply:

| Data Type | User Sees | Counselor Sees | Admin Sees |
|-----------|-----------|----------------|------------|
| Profile info (name, age, gender) | ✅ Own | ✅ Booked clients only | ✅ All |
| Session history | ✅ Own | ✅ Own sessions only | ✅ All |
| Session notes | ❌ | ✅ Own notes only | ❌ |
| Recovery plans (counselor-created) | ✅ Assigned to them | ✅ Created by them | ❌ |
| Recovery plans (self-created) | ✅ Own | ❌ | ❌ |
| Sobriety days / progress tracker | ✅ Own | ❌ | ❌ |
| Daily check-ins (mood, energy, etc.) | ✅ Own | ❌ | ❌ |
| Urge logs | ✅ Own | ❌ | ❌ |
| Journal entries | ✅ Own | ❌ | ❌ |
| Community posts | ✅ Public | ❌ (not in counselor view) | ✅ Moderation |
| Payment transactions | ✅ Own | ✅ Own earnings | ✅ All |
| Achievements | ✅ Own | ❌ | ❌ |

**Key Principles:**
- **Data Minimization**: Counselors only see data necessary for their professional role (demographics + session/plan context)
- **Purpose Limitation**: Self-reported health tracking data (check-ins, urge logs, journals) exists solely for the user's personal benefit
- **No Silent Data Sharing**: If future features allow users to share tracking data with counselors, it must be **explicit opt-in** with clear consent
- **Right to Deletion**: Users can request account deletion; all personal data must be purged (admin retains anonymized transaction records only)

---

## 10. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| PayHere sandbox limitations | Payment testing may differ from production | Test edge cases thoroughly, document sandbox vs. production differences |
| Google Meet API rate limits | Meet link generation may fail during high traffic | Queue requests, retry with backoff, cache links |
| Email deliverability | Emails may land in spam | Use reputable SMTP provider, configure SPF/DKIM |
| Counselor no-show after payment | User frustration, refund burden | Auto-no-show detection, clear refund policy, admin alerts |
| Large file uploads | Server storage/performance | File size limits in .env, validate server-side, plan cloud migration |

---

## 11. Out of Scope (Future)

- Real-time chat (WebSocket-based) between user and counselor
- Mobile native apps (iOS/Android)
- AI-powered recovery plan suggestions
- Video calling within the platform (relying on Google Meet)
- Multi-language support
- Advanced analytics/BI dashboards
- Cloud file storage (planned, `.env` ready)
