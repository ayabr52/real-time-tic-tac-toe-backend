
# Backend `README.md` (Laravel + Reverb)

```markdown
# ⚡ Real-Time Tic-Tac-Toe Backend API (Laravel 11 + Reverb)

A high-performance RESTful API and WebSocket broadcast server powering the online Tic-Tac-Toe game, built using **Laravel 11** and **Laravel Reverb**.

---

## 🛠️ Tech Stack & Architecture

- **Framework:** Laravel 11
- **Real-time Server:** Laravel Reverb (Native WebSockets)
- **Database:** MySQL / PostgreSQL / SQLite
- **Architecture:** REST API + WebSocket Broadcaster pattern

---

## 🛰️ API Endpoint Reference

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/rooms/create` | Creates a new game room |
| `POST` | `/api/rooms/join` | Validates and joins an existing room |
| `GET` | `/api/rooms/{name}` | Fetches current board state and active symbol |
| `POST` | `/api/rooms/{name}/move` | Validates & broadcasts a player move |
| `POST` | `/api/rooms/{name}/reset` | Clears the board and broadcasts reset event |
| `DELETE` | `/api/rooms/{name}` | Destroys room and associated state |

---

## ⚙️ Local Development Setup

### 1. Install Dependencies
```bash
composer install
cp .env.example .env
php artisan key:generate
2. Configure Environment .env
Ensure your .env contains the matching Reverb keys:

Code snippet
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=123456
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
3. Database Migration & Cache Clear
Bash
php artisan migrate
php artisan config:clear


4. Start Application & Broadcast Servers
Open two terminal windows:

Bash
# Terminal 1: Application Server
php artisan serve

# Terminal 2: WebSocket Server
php artisan reverb:start

---
<FollowUp label="Would you like to document any Laravel backend files (Controllers or Events) in English as well?" query="Yes, please document the Laravel GameController and MovePlayed Event in English."/>

Created with ❤️ by IT. Aya
