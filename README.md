# TradeLedger

Personal trading portfolio management application for XAUUSD/forex with Smart Money Concepts framing. Single-user app with multi-account isolation (Real/Demo).

## Tech Stack

- **Backend:** Laravel 12 + PHP 8.2+
- **Frontend:** Livewire 3 + Alpine.js + Tailwind CSS
- **Charts:** ApexCharts
- **Auth:** Laravel Breeze (Livewire stack)
- **Database:** MySQL (Laragon, port 3308)
- **Fonts:** Space Grotesk, Inter, JetBrains Mono

## Features

- **Multi-Account Isolation** — Real & Demo accounts with full data separation via `ActiveAccountScope`
- **Dashboard** — Stat cards, equity curve chart (ApexCharts), target rings, daily trading log table
- **Daily Log** — CRUD entries with profit/loss/day-off status, inline calculator, bulk delete, pagination
- **Target Rules** — Configurable target percentages (target_1/target_2), off-day toggles, full recalculation on save
- **Deposit & Withdrawal** — CRUD with balance validation, auto-recalculation of all targets and daily logs
- **Analytics** — Custom date range, computed stats, grouped bar chart
- **Journal** — Timeline view with inline note editing, month filter
- **Account Settings** — Edit account name, initial/current balance with manual recalculation
- **Dark/Light Mode** — System-wide theme with flash prevention, localStorage persistence
- **Responsive** — Sidebar on desktop, fixed bottom nav on mobile

## Default Credentials

| Email | Password |
|-------|----------|
| `admin@tradeledger.io` | `password` |

## Installation

```bash
# Clone repository
git clone <repository-url>
cd trading.app

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3308
DB_DATABASE=trading_apps
DB_USERNAME=root
DB_PASSWORD=

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (30 days sample data for Real & Demo accounts)
php artisan db:seed

# Build frontend assets
npm run build
```

## Database Schema

```
users
├── id, name, email, password
├── theme_preference (dark|light)
└── active_account_id (FK → accounts)

accounts
├── id, user_id (FK → users)
├── name, type (real|demo)
├── initial_balance, current_balance
└── currency, is_active

daily_logs
├── id, account_id (FK → accounts)
├── log_date (unique per account)
├── status (profit|loss|day_off)
├── balance, daily_percent
├── profit_amount, loss_amount
└── notes

targets
├── id, account_id (FK → accounts)
├── daily_log_id (FK → daily_logs, nullable)
├── target_type (target_1|target_2)
├── target_amount, running_amount
└── target_closing, status

transactions
├── id, account_id (FK → accounts)
├── type (deposit|withdrawal)
├── amount, transaction_date
└── notes

account_rules
├── id, account_id (FK → accounts, unique)
├── target_1_pct, target_2_pct
└── off_days (JSON)
```

## Routes

| URI | Name | Description |
|-----|------|-------------|
| `/dashboard` | `dashboard` | Overview with stat cards, equity curve, daily log |
| `/daily-log` | `daily-log` | CRUD daily trading entries |
| `/target-rules` | `target-rules` | Configure target percentages and off-days |
| `/deposit-withdrawal` | `deposit-withdrawal` | Manage deposits and withdrawals |
| `/analytics` | `analytics` | Performance analytics with date range |
| `/journal` | `journal` | Trading journal timeline |

## Balance Formula

```
Balance = $0 + all deposits - all withdrawals + all P/L from daily logs
```

Recalculated via `TargetCalculationService::recalculateAllForAccount()` on every CRUD operation (daily logs, deposits, withdrawals, account settings changes).

## Project Structure

```
app/
├── Livewire/           # Livewire components
│   ├── DashboardOverview.php
│   ├── DailyLogTable.php
│   ├── TargetRules.php
│   ├── DepositWithdrawal.php
│   ├── Analytics.php
│   ├── Journal.php
│   ├── AccountSwitcher.php
│   └── AccountSettings.php
├── Models/             # Eloquent models
│   ├── Account.php
│   ├── AccountRule.php
│   ├── DailyLog.php
│   ├── Target.php
│   ├── Transaction.php
│   └── Scopes/ActiveAccountScope.php
└── Services/
    └── TargetCalculationService.php

resources/
├── views/
│   ├── layouts/app.blade.php        # Main layout (sidebar, topbar, bottom nav)
│   └── livewire/                    # Livewire component views
├── css/app.css                      # Glass-card styles, light mode overrides
└── js/app.js

database/
├── migrations/       # 10 migrations
├── seeders/          # DatabaseSeeder (30-day sample data)
└── factories/        # Model factories
```

## Notes

- Do not run `php artisan serve` — use Laragon's built-in server
- MySQL runs on port `3308` (Laragon default)
- Reference files in `references/` (mockup HTML, excel.png, prompt MD) are preserved for design reference
