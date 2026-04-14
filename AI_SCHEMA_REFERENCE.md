# NewPath Database Schema Reference

A concise reference for AI models working with the NewPath recovery support platform.

## Project Overview

- **Database**: MySQL (`new_path_2`)
- **Purpose**: Recovery support platform connecting users (recovering individuals) with counselors
- **Core Features**: User management, counselor profiles, session bookings, payments, journaling, community posts, recovery tracking, messaging

---

## Core Tables

### Users & Authentication
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | Central user accounts | `user_id`, `email`, `username`, `role` (user/counselor/admin), `password_hash`, `is_active`, `onboarding_completed` |
| `admin` | Admin profiles linked to users | `admin_id`, `user_id`, `full_name`, `is_super_admin` |
| `user_profiles` | Extended user data | `user_id`, `sobriety_start_date`, `recovery_type`, `motivation_level`, `risk_score`, `is_anonymous` |
| `achievements` | User sobriety milestones | `user_id`, `achievement_type` (e.g., '7_days_sober'), `title`, `days_required`, `earned_at` |

### Counselors
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `counselors` | Counselor profiles | `counselor_id`, `user_id`, `title`, `specialty`, `bio`, `consultation_fee`, `is_verified`, `rating`, `availability_schedule` (JSON) |
| `counselor_applications` | Counselor applications | `application_id`, `email`, `status` (pending/approved/rejected), `reviewed_by`, `admin_notes` |

### Sessions & Bookings
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `sessions` | Counseling appointments | `session_id`, `user_id`, `counselor_id`, `session_datetime`, `duration_minutes`, `session_type` (video/audio/chat/in_person), `status` (scheduled/confirmed/completed/cancelled), `meeting_link`, `rating`, `review` |
| `booking_holds` | Temporary session holds | `hold_id`, `counselor_id`, `user_id`, `slot_datetime`, `status` (held/confirmed/released), `expires_at` |
| `reschedule_requests` | Session reschedule requests | `request_id`, `session_id`, `status` (pending/approved/rejected), `counselor_note` |

### Payments
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `transactions` | Payment records | `transaction_id`, `transaction_uuid`, `session_id`, `user_id`, `counselor_id`, `amount`, `currency`, `payment_type` (session/subscription/tip), `status` (pending/completed/failed/refunded), `payhere_order_id`, `stripe_payment_intent_id` |
| `payment_methods` | User payment methods | `payment_method_id`, `user_id`, `method_type` (card/paypal/bank_transfer), `stripe_payment_method_id`, `is_default` |
| `counselor_payouts` | Counselor payouts | `payout_id`, `counselor_id`, `amount`, `status` (pending/processing/completed), `platform_commission` |

### Recovery Tracking
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `recovery_plans` | Recovery plans | `plan_id`, `user_id`, `counselor_id`, `title`, `plan_type` (counselor/self), `status` (draft/active/paused/completed), `progress_percentage`, `assigned_status` |
| `recovery_goals` | Goals within plans | `goal_id`, `plan_id`, `goal_type` (short_term/long_term), `title`, `target_days`, `status`, `achieved_at` |
| `recovery_tasks` | Tasks within plans | `task_id`, `plan_id`, `title`, `task_type` (journal/meditation/session/exercise/custom), `status`, `priority`, `due_date`, `phase` |
| `daily_checkins` | Daily mood/sober tracking | `checkin_id`, `user_id`, `checkin_date`, `is_sober`, `mood_rating`, `mood_label`, `energy_level`, `stress_level` |
| `user_progress` | Sobriety progress | `progress_id`, `user_id`, `date`, `days_sober`, `is_sober_today`, `milestone_progress` |
| `relapse_history` | Relapse records | `relapse_id`, `user_id`, `relapse_date`, `previous_streak_days`, `reason` |
| `urge_logs` | Urge/craving tracking | `urge_id`, `user_id`, `intensity`, `trigger_category`, `outcome` (resisted/relapsed/in_progress) |

### Journaling
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `journal_categories` | Entry categories | `category_id`, `name`, `slug`, `is_default` (default: Gratitude, Progress, Challenge, Reflection, Other) |
| `journal_entries` | Journal entries | `entry_id`, `user_id`, `category_id`, `title`, `content`, `mood`, `is_private`, `is_highlight` |

### Community
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `community_posts` | Forum posts | `post_id`, `user_id`, `title`, `content`, `post_type` (general/success_story/question/support_request/resource), `is_anonymous`, `likes_count`, `comments_count`, FULLTEXT on (title, content) |
| `post_comments` | Post comments | `comment_id`, `post_id`, `user_id`, `parent_comment_id`, `content`, `is_anonymous` (supports nested via parent_comment_id) |
| `post_likes` | Post likes | `post_id`, `user_id` (unique) |
| `post_tags` | Tag definitions | `tag_id`, `name`, `slug`, `post_count` |
| `post_tag_mappings` | Post-tag relationships | `post_id`, `tag_id` |
| `post_reports` | Report moderation | `report_id`, `post_id`, `comment_id`, `reporter_id`, `reason`, `status` (pending/reviewed/resolved/dismissed) |

### Messaging
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `dm_conversations` | DM conversations | `conversation_id`, `user1_id`, `user2_id`, `last_message_at`, `last_message_preview` (unique on user pair) |
| `direct_messages` | Messages | `message_id`, `conversation_id`, `sender_id`, `content`, `is_read` |
| `session_messages` | In-session chat | `message_id`, `session_id`, `sender_id`, `message` |
| `notifications` | User notifications | `notification_id`, `user_id`, `type` (followup_message/booking_confirmed/reschedule_request etc), `title`, `message`, `link`, `is_read` |

### Support Groups
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `support_groups` | Group definitions | `group_id`, `name`, `description`, `category`, `meeting_link`, `is_active` |
| `support_group_members` | Group membership | `group_id`, `user_id`, `role` (member/moderator/leader) |
| `support_group_messages` | Group chat | `message_id`, `group_id`, `user_id`, `content`, `is_pinned` |

### Other
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `help_centers` | Resource directory | `help_center_id`, `name`, `type`, `category`, `phone_number`, `address`, `city`, `is_active` |
| `job_posts` | Job listings | `job_id`, `title`, `company`, `location`, `job_type`, `is_active`, `created_by` (admin) |
| `user_connections` | User connections | `connection_id`, `user_id`, `connected_user_id`, `status` (pending/accepted/declined/blocked) |
| `audit_logs` | Action logging | `log_id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address` |
| `system_settings` | Config settings | `setting_id`, `setting_key`, `setting_value` |

---

## Key Relationships

```
users (role: user/counselor/admin)
    ├── users.role = 'counselor' → counselors (via user_id)
    ├── users.role = 'admin' → admin (via user_id)
    ├── sessions (user_id → user, counselor_id → counselors)
    ├── recovery_plans (user_id)
    ├── journal_entries (user_id)
    ├── community_posts (user_id)
    └── transactions (user_id)
```

---

## Common Query Patterns

### User Search (fallback chain)
```sql
COALESCE(display_name, CONCAT(first_name, ' ', last_name), username, email) LIKE '%search%'
```

### Full-Text Post Search
```sql
MATCH(title, content) AGAINST('search terms' IN NATURAL LANGUAGE MODE)
```

### Active User Count by Role
```sql
SELECT role, COUNT(*) FROM users WHERE is_active = 1 GROUP BY role
```

### Recent Sessions
```sql
SELECT * FROM sessions WHERE session_datetime > NOW() ORDER BY session_datetime ASC
```

---

## Naming Conventions

- **Tables**: snake_case (e.g., `recovery_plans`, `community_posts`)
- **Columns**: snake_case (e.g., `user_id`, `created_at`, `is_active`)
- **Primary Keys**: `{table}_id` (e.g., `user_id`, `session_id`)
- **Foreign Keys**: `{related_table}_id` (e.g., `counselor_id`, `plan_id`)
- **Timestamps**: `created_at`, `updated_at`
- **Booleans**: `is_` prefix (e.g., `is_active`, `is_sober`, `is_anonymous`)
- **JSON columns**: `availability_schedule`, `notification_preferences`, `privacy_settings`

---

## Enum Reference

| Column | Values |
|--------|--------|
| `users.role` | user, counselor, admin |
| `sessions.status` | scheduled, confirmed, in_progress, completed, cancelled, no_show |
| `sessions.session_type` | video, audio, chat, in_person |
| `transactions.status` | pending, completed, failed, refunded, disputed |
| `transactions.payment_type` | session, subscription, tip, refund |
| `recovery_plans.status` | draft, active, paused, completed, cancelled |
| `recovery_plans.plan_type` | counselor, self |
| `community_posts.post_type` | general, success_story, question, support_request, resource |
| `post_reports.status` | pending, reviewed, resolved, dismissed |
| `reschedule_requests.status` | pending, approved, rejected |

---

## Notes

- All tables use InnoDB engine with utf8mb4_unicode_ci charset
- Most tables have `created_at` and `updated_at` timestamps with auto-update
- Soft delete patterns: `is_active` flag rather than hard deletes
- Counselor verification: `counselors.is_verified` (0/1)
- User onboarding: `users.onboarding_completed`, `users.current_onboarding_step`