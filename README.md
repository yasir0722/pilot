# Pilot

Personal life management system — Finance, Groceries, Habits, Reminders — with Laravel API, Vue 3 frontend, and Telegram bot.

## Quick Start

```bash
# Build and start all services
docker compose up -d --build

# Install PHP dependencies
docker compose exec app composer install

# Generate app key
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate

# Create storage link
docker compose exec app php artisan storage:link

# Install & build frontend
docker compose run --rm node sh -c "npm install && npm run build"
```

Access the app at **http://localhost:8080**

## Development

```bash
# Start Vite dev server (HMR)
docker compose up node

# Run queue worker
docker compose up queue

# Run scheduler
docker compose up scheduler

# Tail logs
docker compose logs -f app
```

## Telegram Bot

1. Create a bot via [@BotFather](https://t.me/BotFather)
2. Set `TELEGRAM_BOT_TOKEN` in `.env`
3. Set webhook: `docker compose exec app php artisan telegram:set-webhook`

### Bot Commands

| Command | Action |
|---------|--------|
| `rm12 lunch` | Add RM12 expense for lunch |
| `add milk` | Add milk to grocery list |
| `done meditate` | Mark habit as done today |
| `summary` | Get daily summary |
| `help` | Show available commands |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/summary` | Dashboard summary |
| GET/POST | `/api/expenses` | List/create expenses |
| POST | `/api/expenses/import-csv` | Import CSV expenses |
| POST | `/api/receipts` | Upload receipt |
| GET/POST | `/api/groceries` | List/create grocery lists |
| GET/POST | `/api/habits` | List/create habits |
| POST | `/api/habits/{id}/complete` | Mark habit complete |
| GET/POST | `/api/reminders` | List/create reminders |

## Architecture

```
pilot/
├── app/
│   ├── Http/Controllers/Api/   # API controllers
│   ├── Models/                 # Eloquent models
│   ├── Services/               # Telegram service + parser
│   ├── Jobs/                   # Queue jobs (reminders, habits)
│   └── Console/Commands/       # Artisan commands
├── database/migrations/        # DB schema
├── resources/js/               # Vue 3 SPA
│   ├── pages/                  # Page components
│   ├── App.vue                 # Root component
│   ├── routes.js               # Vue Router config
│   └── api.js                  # Axios instance
├── docker/                     # Docker configs
│   ├── app/Dockerfile          # PHP-FPM
│   └── nginx/default.conf      # Nginx
├── docker-compose.yml          # All services
└── public/
    ├── manifest.json           # PWA manifest
    └── sw.js                   # Service worker
```
