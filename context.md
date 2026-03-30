# Pilot — Project Context

> Personal management system: Finance, Groceries, Habits, Reminders, Telegram bot.
> Last updated: March 30, 2026

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13.2 / PHP 8.4-fpm-alpine |
| Frontend | Vue 3.5 + Vue Router 4.5 + Vite 8 |
| Styling | Tailwind CSS v4 (`@tailwindcss/vite`) |
| Auth | Laravel Sanctum v4 (API tokens via Bearer header) |
| Database | MySQL 8.0 |
| Cache/Queue | Redis 7-alpine |
| Web server | nginx 1.25-alpine |
| Node (dev) | Node 20-alpine (Vite HMR) |
| CSV import | league/csv ^9 |

---

## Docker Services

| Service name | Image | Purpose | Ports |
|---|---|---|---|
| `pilot-app` | php:8.4-fpm-alpine (custom) | Laravel PHP-FPM | — |
| `pilot-web` | nginx:1.25-alpine | HTTP server | **8080:80** |
| `pilot-db` | mysql:8.0 | Database | **3307:3306** |
| `pilot-redis` | redis:7-alpine | Queue + Cache | **6380:6379** |
| `pilot-queue` | same as app | Queue worker | — |
| `pilot-scheduler` | same as app | Artisan scheduler | — |
| `pilot-node` | node:20-alpine | Vite dev server | **5173:5173** |

**Access the app:** http://localhost:8080

---

## Database Credentials (HeidiSQL / TablePlus)

| Field | Value |
|---|---|
| Host | `localhost` |
| Port | `3307` |
| User | `pilot` |
| Password | `secret` |
| Database | `pilot` |

---

## User Account

| Field | Value |
|---|---|
| Name | Yasir Azman |
| Email | yasirazman96@gmail.com |
| Password | `password` |

---

## Authentication

- **Method:** Laravel Sanctum — API token stored in `localStorage`
- **Login:** `POST /api/login` → returns `{ user, token }`
- **Logout:** `POST /api/logout` (Bearer token required)
- **Me:** `GET /api/me` (Bearer token required)
- All API routes except `/api/login` and `/api/telegram/webhook` require `Authorization: Bearer <token>`
- Frontend intercepts 401 responses → clears token → redirects to `/login`

---

## API Routes

### Public
| Method | Path | Action |
|---|---|---|
| POST | `/api/login` | Authenticate, returns token |
| POST | `/api/telegram/webhook` | Telegram bot webhook |

### Protected (Bearer token)
| Method | Path | Controller |
|---|---|---|
| POST | `/api/logout` | AuthController |
| GET | `/api/me` | AuthController |
| GET | `/api/summary` | SummaryController |
| CRUD | `/api/expenses` | ExpenseController |
| POST | `/api/expenses/import-csv` | ExpenseController |
| GET | `/api/expenses-summary` | ExpenseController |
| POST/GET/DELETE | `/api/receipts` | ReceiptController |
| PATCH | `/api/receipts/{id}/link` | ReceiptController |
| CRUD | `/api/groceries` | GroceryController |
| POST | `/api/groceries/{id}/items` | GroceryController |
| POST | `/api/groceries/{id}/clone` | GroceryController |
| PATCH | `/api/grocery-items/{id}/toggle` | GroceryController |
| CRUD | `/api/habits` | HabitController |
| POST/DELETE | `/api/habits/{id}/complete` | HabitController |
| CRUD | `/api/reminders` | ReminderController |

---

## Database Schema

### `users`
`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `timestamps`

### `personal_access_tokens` (Sanctum)
Standard Sanctum table

### `expenses`
`id`, `amount` (decimal 10,2), `category`, `description` (nullable), `spent_at` (date), `receipt_id` (FK nullable), `timestamps`

### `receipts`
`id`, `path`, `filename`, `expense_id` (FK nullable), `timestamps`

### `grocery_lists`
`id`, `name`, `is_template` (bool), `timestamps`

### `grocery_items`
`id`, `grocery_list_id` (FK), `name`, `is_checked` (bool), `timestamps`

### `habits`
`id`, `name`, `frequency` (daily/weekly), `timestamps`

### `habit_completions`
`id`, `habit_id` (FK), `completed_at` (date), `timestamps`

### `reminders`
`id`, `title`, `remind_at` (datetime), `is_sent` (bool), `timestamps`

---

## File Structure (key files)

```
c:\Dev\pilot\
├── app/
│   ├── Console/Commands/SetTelegramWebhook.php
│   ├── Http/Controllers/Api/
│   │   ├── AuthController.php       ← NEW (login/logout/me)
│   │   ├── ExpenseController.php
│   │   ├── GroceryController.php
│   │   ├── HabitController.php
│   │   ├── ReceiptController.php
│   │   ├── ReminderController.php
│   │   ├── SummaryController.php
│   │   └── TelegramController.php
│   ├── Jobs/
│   │   ├── SendHabitReminders.php
│   │   └── SendReminderNotifications.php
│   ├── Models/
│   │   ├── User.php                 ← HasApiTokens trait added
│   │   ├── Expense.php
│   │   ├── Receipt.php
│   │   ├── GroceryList.php
│   │   ├── GroceryItem.php
│   │   ├── Habit.php
│   │   ├── HabitCompletion.php
│   │   └── Reminder.php
│   └── Services/
│       ├── TelegramService.php
│       └── TelegramCommandParser.php
├── database/
│   ├── migrations/                  ← all 9 migrations ran
│   └── seeders/DatabaseSeeder.php   ← seeds Yasir Azman user
├── docker/
│   ├── app/Dockerfile               ← php:8.4-fpm-alpine
│   └── nginx/default.conf
├── resources/js/
│   ├── app.js                       ← Vue + Router + nav guard
│   ├── api.js                       ← Axios + Bearer token interceptor
│   ├── routes.js                    ← /login (guest), all others (auth)
│   ├── App.vue                      ← nav with user name + logout
│   └── pages/
│       ├── Login.vue                ← NEW
│       ├── Dashboard.vue
│       ├── Expenses.vue
│       ├── Groceries.vue
│       ├── Habits.vue
│       └── Reminders.vue
├── routes/
│   ├── api.php                      ← protected with auth:sanctum
│   └── console.php                  ← scheduled jobs
├── docker-compose.yml
└── .env
```

---

## Telegram Bot

| Setting | Value |
|---|---|
| Bot name | `@xxx` |
| Token | `xxx` |
| Webhook | `https://6aee-2001-e68-541a-e875-61ba-aae9-e940-adf9.ngrok-free.app/api/telegram/webhook` |

> ⚠️ ngrok free tier URL changes on restart. When it changes:
> 1. Update `TELEGRAM_WEBHOOK_URL` in `.env`
> 2. Run: `docker exec pilot-app php artisan config:clear`
> 3. Run: `docker exec pilot-app php artisan telegram:set-webhook`

### Supported bot commands
| Command | Action |
|---|---|
| `rm12 lunch` | Add expense of RM 12, category "lunch" |
| `add milk` | Add grocery item "milk" |
| `done meditate` | Mark habit "meditate" as completed |
| `summary` | Show today's summary |
| `help` | Show command list |

---

## Scheduled Jobs (every minute)

- `SendReminderNotifications` — sends Telegram messages for due reminders
- `SendHabitReminders` — sends daily habit prompts

---

## Environment (.env key values)

```dotenv
APP_URL=http://localhost:8080
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pilot
DB_USERNAME=pilot
DB_PASSWORD=secret
REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
TELEGRAM_BOT_TOKEN=8763417220:AAHb5mqM_7aT_u1PcbwcwC6qg7qejqJ9evo
TELEGRAM_WEBHOOK_URL=https://6aee-2001-e68-541a-e875-61ba-aae9-e940-adf9.ngrok-free.app/api/telegram/webhook
```

---

## Useful Docker Commands

```bash
# Start all containers
docker compose up -d

# Stop all
docker compose down

# Run migrations
docker exec pilot-app php artisan migrate

# Re-seed user
docker exec pilot-app php artisan db:seed

# Clear config cache
docker exec pilot-app php artisan config:clear

# Set Telegram webhook
docker exec pilot-app php artisan telegram:set-webhook

# View logs
docker logs pilot-app
docker logs pilot-node
docker logs pilot-web

# Open shell
docker exec -it pilot-app sh
```

---

## Phase 1 Status — COMPLETE ✅

- [x] Docker infrastructure (7 containers)
- [x] Laravel 13 installed (PHP 8.4)
- [x] All 9 database migrations ran
- [x] All 7 Eloquent models with relationships
- [x] All API controllers (CRUD + CSV import + receipt upload)
- [x] Telegram webhook + command parser
- [x] Queue jobs + scheduler
- [x] `SetTelegramWebhook` artisan command
- [x] Vue 3 SPA with 5 pages + router
- [x] PWA manifest + service worker
- [x] Vite dev server on localhost:5173
- [x] **Authentication (Laravel Sanctum + Login page)**
- [x] **User seeded: Yasir Azman / yasirazman96@gmail.com**

---

## Phase 2 — Planned Next Steps

> To be defined. Possible directions:

### UX / Polish
- [ ] Improve Expenses page: inline add form, category filter, date range picker
- [ ] Improve Groceries: drag-to-reorder items, template management UI
- [ ] Improve Habits: weekly grid / streak view
- [ ] Improve Reminders: datetime picker, repeat option (daily/weekly)
- [ ] Dashboard: charts (spending by category, habit streaks)

### Backend / Data
- [ ] Add `user_id` FK to all tables → multi-user support
- [ ] Pagination on expenses list (large data sets)
- [ ] Expense categories CRUD (configurable, not hardcoded)
- [ ] Receipt OCR / auto-parse amount from image

### Telegram
- [ ] Full two-way conversation flow (sessions per user)
- [ ] Link Telegram user ID to app user account
- [ ] `/list` command — show today's grocery list
- [ ] `/habits` command — show today's habit status

### Infrastructure / Quality
- [ ] Production Docker build (nginx serves compiled assets, no Vite server)
- [ ] Add `user_id` index on all main tables
- [ ] Write API tests (Pest)
- [ ] CI/CD (GitHub Actions)
- [ ] HTTPS on production (Let's Encrypt / Cloudflare Tunnel)
