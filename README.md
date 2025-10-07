# 💰 PGold API

PGold API is a **Laravel Modular Application** built to manage authentication and currency rate services (Gift Cards and Crypto).  
It follows a **modular architecture**, allowing each domain feature to exist independently for cleaner structure and scalability.

---

## 🧩 Project Structure

The project uses **Laravel Modules** (via `nwidart/laravel-modules`) to separate core features.

```
Modules/
│
├── Auth/
│   ├── Http/
│   │   ├── Controllers/AuthController.php
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Routes/api.php
│   ├── Models/
│   └── ...
│
└── Rates/
    ├── Http/
    │   ├── Controllers/RatesController.php
    │   ├── Requests/
    │   └── Resources/
    ├── Routes/api.php
    ├── Models/
    └── ...
```

---

## ⚙️ Tech Stack

| Layer | Technology |
|-------|-------------|
| **Backend Framework** | Laravel 10 (Modular architecture) |
| **Language** | PHP 8.2 |
| **Database** | SQL Server / MySQL (configurable) |
| **Authentication** | Laravel Sanctum |
| **Containerization** | Docker & Docker Compose |
| **Package Manager** | Composer |
| **Code Style** | PSR-12 |

---

## 🚀 Getting Started

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/geomartins/pgold-api.git
cd pgold-api
```

### 2️⃣ Install Dependencies

```bash
composer install
```

### 3️⃣ Copy Environment File

```bash
cp .env.example .env
```

Then update your `.env` variables (DB, APP_URL, etc.).

### 4️⃣ Generate Application Key

```bash
php artisan key:generate
```

### 5️⃣ Run Database Migrations

```bash
php artisan migrate
```

### 6️⃣ Run with Docker (Optional)

```bash
docker compose up -d
```

---

## 🧠 API Routes

### 🔐 Auth Module (`/api/auth`)

| Method | Endpoint | Description | Middleware |
|---------|-----------|-------------|-------------|
| `POST` | `/auth/check-username` | Check if a username is available | None |
| `POST` | `/auth/register` | Register a new user | None |
| `POST` | `/auth/login` | Authenticate user and issue token | None |
| `GET` | `/auth/me` | Get current authenticated user | `auth:sanctum` |
| `POST` | `/auth/logout` | Logout current user | `auth:sanctum` |

---

### 💱 Rates Module (`/api/rates`)

| Method | Endpoint | Description |
|---------|-----------|-------------|
| `GET` | `/rates/gift-cards` | Retrieve gift card rate information |
| `GET` | `/rates/crypto` | Retrieve cryptocurrency rate information |

---

## 🧩 Module Responsibilities

### **Auth Module**
- Handles user registration, login, and logout
- Issues API tokens via Sanctum
- Provides authenticated user details (`/me`)

### **Rates Module**
- Fetches dynamic gift card rates and available countries/categories
- Fetches cryptocurrency rates for supported coins
- Provides modular, reusable endpoints for the frontend (e.g., Flutter app)

---

## 🧪 Running the API Locally

```bash
php artisan serve
```

API will be available at:  
👉 `http://localhost:8000/api`

---

## 🧰 Useful Artisan Commands

| Command | Description |
|----------|-------------|
| `php artisan module:list` | View all loaded modules |
| `php artisan module:make <name>` | Create a new module |
| `php artisan optimize` | Optimize framework for production |
| `php artisan route:list` | List all registered routes |

---

## 🧑‍💻 Author

**Martins Abiodun**  
Developer  
🔗 [GitHub: geomartins](https://github.com/geomartins)

---

## 🛡 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.
