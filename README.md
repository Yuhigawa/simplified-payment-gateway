# Simplified Payment Gateway

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Hyperf](https://img.shields.io/badge/Hyperf-3.1-3C873A?style=for-the-badge&logo=php&logoColor=white)
![Swoole](https://img.shields.io/badge/Swoole-5.0-003366?style=for-the-badge&logo=swoole&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?style=for-the-badge&logo=postgresql&logoColor=white)
![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg?style=for-the-badge)

A robust, high-performance RESTful payment gateway built with the [Hyperf](https://hyperf.wiki/) framework. This system enables secure money transfers between users and shopkeepers, leveraging a modular monolith architecture.

---

## 🚀 Quick Start

The fastest way to get verified and running is using **Docker**.

```bash
# 1. Clone the repository
git clone git@github.com:Yuhigawa/simplified-payment-gateway.git
cd simplified-payment-gateway

# 2. Start services (App, Postgres, Redis)
docker-compose up -d

# 3. Install dependencies & Run migrations
docker-compose exec app composer install
docker-compose exec app php bin/hyperf.php migrate

# 4. Access the API
curl http://localhost:9501
```

---

## 📋 Features

- **User Accounts**: Registration for individual users (CPF) and shopkeepers (CNPJ).
- **Secure Transfers**: Atomic money transfers with rollback support.
- **Validation**: Strict balance checks and user type restrictions.
- **External Integration**:
  - **Authorization**: Pre-transfer validation via external service.
  - **Notifications**: Post-transfer async notifications (email/SMS).
- **Robust Architecture**: Modular Monolith design using DDD principles.

---

## 🏗️ Architecture & Stack

**Structure**: Modular Monolith (separate `Account` and `Transfer` modules).

| Component | Technology | Description |
|-----------|------------|-------------|
| **Framework** | Hyperf 3.1 | High-performance coroutine framework |
| **Runtime** | Swoole 5.0+ | Coroutine-based concurrency |
| **Database** | PostgreSQL 16 | ACID compliant relational storage |
| **Cache** | Redis / Dragonfly | Fast key-value store |
| **Architecture** | DDD / Modular | Clean separation of concerns |

### Project Structure
```
app/
├── Module/
│   ├── Account/          # User management domain
│   └── Transfer/         # Transaction processing domain
├── Controller/           # Shared controllers
├── Exception/            # Global exception handling
└── Model/                # Shared data models
```

---

## 📡 API Reference

### 👤 User Management

#### Create User
**POST** `/api/v1/accounts/users`

<details>
<summary>View Request & Response</summary>

**Request Body**
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "securepassword123",
  "document": "12345678901",
  "document_type": "cpf",
  "balance": 10000
}
```

**Response (201 Created)**
```json
{
  "id": "1234567890123456789",
  "name": "John Doe",
  "balance": 100.00,
  "created_at": "2025-12-22T20:41:26.000000Z"
}
```
</details>

#### Get User Details
**GET** `/api/v1/accounts/{userId}`

---

### 💸 Money Transfers

#### Transfer Funds
**POST** `/api/v1/transactions/transfer`

<details>
<summary>View Request & Response</summary>

**Request Body**
```json
{
  "value": 100.0,
  "payer": 4,
  "payee": 15
}
```

- **Payer**: ID of sending user (CFP only).
- **Payee**: ID of receiving user/shopkeeper.
- **Value**: Amount in currency units.

**Response (200 OK)**
```json
{
  "id": "123456789...",
  "status": "completed",
  "value": 100.0,
  "payer": { "id": "4", "name": "John Doe" },
  "payee": { "id": "15", "name": "Shopkeeper Inc" }
}
```
</details>

---

## 🛠️ Development & Testing

Useful commands for local development:

```bash
# Run Tests
docker-compose exec app composer test

# Code validation
docker-compose exec app make ci

# Code Style Fixer
docker-compose exec app make cs-fix

# Watch Mode (Hot Reload)
$HF server:watch
```

## 🔐 Business Rules

1.  **Identity**: CPF/CNPJ and Emails must be unique.
2.  **Concurrency**: Balance updates are atomic; failed transfers rollback all changes.
3.  **Permissions**: Shopkeepers (CNPJ) *receive* only; Users (CPF) can *send* and *receive*.
4.  **External dependencies**: Transfers require external authorization. Notifications are best-effort.

## 📄 License

This project is licensed under the **Apache-2.0** License.
