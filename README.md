# Simplified Payment Gateway

A RESTful payment gateway system built with Hyperf framework that enables money transfers between users and shopkeepers (lojistas).

## 📋 Overview

This system provides a simplified payment gateway that allows:
- User registration with CPF (individual users) or CNPJ (shopkeepers)
- Money transfers between users
- Money transfers from users to shopkeepers
- Transfer authorization via external service
- Payment notifications via external service

## 🎯 Features

### User Management
- **Two types of users:**
  - Regular users (CPF) - can send and receive money
  - Shopkeepers (CNPJ) - can only receive money, cannot send
- **User data required:**
  - Full Name (Nome Completo)
  - CPF or CNPJ (document)
  - Email
  - Password
- **Uniqueness constraints:**
  - CPF/CNPJ must be unique in the system
  - Email addresses must be unique in the system

### Money Transfers
- Users can send money to other users and shopkeepers
- Shopkeepers can only receive transfers, they cannot send money
- Balance validation before transfer
- Transfer-based operations (rollback on failure)
- External authorization service integration
- Payment notification service integration

### External Services Integration
- **Authorization Service:** `https://util.devi.tools/api/v2/authorize` (GET)
  - Called before finalizing any transfer
  - Transfer is only completed if authorization is successful
- **Notification Service:** `https://util.devi.tools/api/v1/notify` (POST)
  - Sends notifications (email/SMS) to recipients after successful transfers
  - Service may be unavailable/unstable, but transfer should still complete

## 🏗️ Architecture

This project follows a **Modular Monolith** architecture pattern with Domain-Driven Design (DDD) principles. The application is organized into self-contained modules, each responsible for a specific business domain:

- **Account Module**: Handles user management, registration, and user-related operations
- **Transfer Module**: Manages money transfers, Transfer processing, and payment operations

Each module is independently structured with its own Domain, Application, Infrastructure, and Presentation layers, allowing for clear separation of concerns while maintaining a single deployable unit.

```
app/
├── Module/
│   ├── Account/          # Account module - User management
│   │   ├── Domain/        # Domain entities, value objects, exceptions
│   │   ├── Application/   # Application services
│   │   ├── Infra/         # Infrastructure (repositories, external services)
│   │   └── Presentation/  # Controllers, requests, resources
│   └── Transfer/       # Transfer module - Money transfers
│       ├── Domain/        # Transfer domain logic
│       ├── Application/   # Transfer services
│       ├── Infra/         # Transfer repositories, external integrations
│       └── Presentation/  # Transfer controllers, requests, resources
├── Controller/            # Base controllers
├── Exception/             # Exception handlers
└── Model/                 # Base model classes
```

### Technology Stack
- **Framework:** Hyperf 3.1
- **PHP:** >= 8.1
- **Database:** PostgreSQL 16
- **Cache:** Redis/DragonflyDB
- **Server:** Swoole 5.0+

## 📦 Requirements

### System Requirements
- PHP >= 8.1
- Swoole PHP extension >= 5.0 (with `swoole.use_shortname` set to `Off` in `php.ini`)
- JSON PHP extension
- Pcntl PHP extension
- OpenSSL PHP extension
- PDO PHP extension
- Redis PHP extension
- Composer

### Docker Requirements (Recommended)
- Docker
- Docker Compose

## 🚀 Installation

### Using Docker (Recommended)

1. Clone the repository:
```bash
git clone git@github.com:Yuhigawa/simplified-payment-gateway.git
cd simplified-payment-gateway
```

2. Start the services:
```bash
docker-compose up -d
```

This will start:
- Application server (port 9501)
- PostgreSQL database (port 5432)
- Redis/DragonflyDB (port 6379)

3. Install dependencies:
```bash
docker-compose exec app composer install
```

4. Run migrations:
```bash
docker-compose exec app php bin/hyperf.php migrate
```

5. The API will be available at `http://localhost:9501`

### Manual Installation

1. Install PHP dependencies:
```bash
composer install
```

2. Configure environment variables (copy `.env.example` to `.env` and adjust):
```bash
cp .env.example .env
```

3. Set up PostgreSQL database and update `.env` with database credentials

4. Run migrations:
```bash
php bin/hyperf.php migrate
```

5. Start the server:
```bash
php bin/hyperf.php start
```

## 🔧 Configuration

### Environment Variables

Key environment variables in `.env`:

```env
APP_ENV=dev
APP_NAME=simplified-payment-gateway

# Database
DB_DRIVER=pgsql
POSTGRES_HOST=db
POSTGRES_PORT=5432
POSTGRES_DATABASE=hyperf_database
POSTGRES_USER=hyperf_user
POSTGRES_PASSWORD=hyperf_password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
```

## 📡 API Endpoints

### Base URL
```
http://localhost:9501
```

### User Management

#### Create User
```http
POST /api/v1/accounts/users
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "securepassword123",
  "document": "12345678901",
  "document_type": "cpf",
  "balance": 10000
}
```

**Response:**
```json
{
  "id": "1234567890123456789",
  "name": "John Doe",
  "email": "john.doe@example.com",
  "document": "12345678901",
  "document_type": "cpf",
  "balance": 100.00,
  "created_at": "2025-12-22T20:41:26.000000Z",
  "updated_at": "2025-12-22T20:41:26.000000Z"
}
```

**Validation Rules:**
- `name`: required, string, max 200 characters
- `email`: required, valid email, max 200 characters, unique
- `password`: required, string, max 200 characters
- `document`: required, string, max 14 characters, unique
- `document_type`: required, must be `cpf` or `cnpj`
- `balance`: required, integer (stored in cents)

#### Get User
```http
GET /api/v1/accounts/{userId}
```

**Response:**
```json
{
  "id": "1234567890123456789",
  "name": "John Doe",
  "email": "john.doe@example.com",
  "document": "12345678901",
  "document_type": "cpf",
  "balance": 10000,
  "created_at": "2025-12-22T20:41:26.000000Z",
  "updated_at": "2025-12-22T20:41:26.000000Z"
}
```

### Money Transfers

#### Transfer Money
```http
POST /api/v1/transactions/transfer
Content-Type: application/json

{
  "value": 100.0,
  "payer": 4,
  "payee": 15
}
```

**Request Body:**
- `value`: Transfer amount (float, in currency units)
- `payer`: ID of the user sending money
- `payee`: ID of the user/shopkeeper receiving money

**Transfer Flow:**
1. Validates payer exists and has sufficient balance
2. Validates payee exists
3. Validates payer is not a shopkeeper (shopkeepers cannot send money)
4. Calls external authorization service (`GET https://util.devi.tools/api/v2/authorize`)
5. If authorized, executes transfer within a database Transfer:
   - Deducts amount from payer's balance
   - Adds amount to payee's balance
6. Calls notification service (`POST https://util.devi.tools/api/v1/notify`) to notify recipient
7. If any step fails, Transfer is rolled back and money returns to payer

**Response (Success):**
```json
{
  "id": "1234567890123456789",
  "payer": {
    "id": "4",
    "name": "John Doe",
    "email": "john@example.com",
    "document": "12345678901",
    "document_type": "cpf",
    "balance": "50.00"
  },
  "payee": {
    "id": "15",
    "name": "Jane Doe",
    "email": "jane@example.com",
    "document": "98765432100",
    "document_type": "cpf",
    "balance": "150.00"
  },
  "value": 100.0,
  "status": "completed",
  "created_at": "2025-12-22T20:41:26.000000Z"
}
```

**Response (Error):**
```json
{
  "error": "Insufficient balance",
  "message": "User does not have enough balance to complete the transfer"
}
```

## 🗄️ Database Schema

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    document VARCHAR(14) UNIQUE NOT NULL,
    document_type ENUM('cpf', 'cnpj') NOT NULL,
    balance BIGINT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Notes:**
- `id`: Snowflake ID (string representation of big integer)
- `balance`: Stored in cents (integer)
- `document`: CPF (11 digits) or CNPJ (14 digits)
- `document_type`: `cpf` for regular users, `cnpj` for shopkeepers

### Transactions Table
```sql
CREATE TABLE Transfers (
    id BIGINT PRIMARY KEY,
    value BIGINT NOT NULL,
    payer_id BIGINT NOT NULL,
    payee_id BIGINT NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (payer_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (payee_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_payer_id (payer_id),
    INDEX idx_payee_id (payee_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

**Notes:**
- `id`: Snowflake ID (string representation of big integer)
- `value`: Stored in cents (integer)
- `status`: Transfer status (`pending`, `completed`, `failed`)

## 🔐 Business Rules

1. **User Registration:**
   - CPF/CNPJ and email must be unique
   - Password is hashed using bcrypt
   - Email is normalized (lowercase, trimmed)

2. **Money Transfers:**
   - Only users with `document_type = 'cpf'` can send money
   - Users with `document_type = 'cnpj'` (shopkeepers) can only receive
   - Payer must have sufficient balance
   - All transfers are atomic (Transfer-based)
   - External authorization is required before transfer
   - Notification is sent after successful transfer (non-blocking)

3. **Transfer Safety:**
   - All database operations are wrapped in Transfers
   - On any failure, changes are rolled back
   - Balance is restored to payer if transfer fails

## 🧪 Testing

Run tests using PHPUnit:

```bash
# Using Docker
docker-compose exec app composer test

# Manual
composer test
```

Test files are located in the `test/` directory.

## 📝 Development

### Code Style

Fix code style issues:
```bash
composer cs-fix
```

### Static Analysis

Run PHPStan:
```bash
composer analyse
```

### Watch Mode (Development)

Start the server in watch mode for auto-reload:
```bash
php bin/hyperf.php server:watch
```

## 🔌 External Services

### Authorization Service
- **Endpoint:** `https://util.devi.tools/api/v2/authorize`
- **Method:** GET
- **Purpose:** Authorize money transfers
- **Behavior:** Transfer only proceeds if authorization is successful

### Notification Service
- **Endpoint:** `https://util.devi.tools/api/v1/notify`
- **Method:** POST
- **Purpose:** Send payment notifications (email/SMS) to recipients
- **Behavior:** Non-blocking - transfer completes even if notification fails

## 🐛 Error Handling

The application includes comprehensive error handling:
- Validation errors return 422 status with detailed messages
- Business logic errors return appropriate HTTP status codes
- Database errors are logged and handled gracefully
- External service failures are handled with appropriate fallbacks

## 📚 Project Structure

The project is organized as a **Modular Monolith** with two main modules:

```
.
├── app/
│   ├── Module/
│   │   ├── Account/          # Account Module - User management
│   │   └── Transfer/       # Transfer Module - Transfer operations
│   ├── Controller/           # Base controllers
│   ├── Exception/            # Exception handlers
│   └── Model/                # Base models
├── config/                   # Configuration files
│   └── autoload/
│       └── modules.php       # Module configuration
├── migrations/               # Database migrations
├── test/                     # Test files
├── bin/                      # Executable scripts
├── runtime/                  # Runtime files
├── vendor/                   # Composer dependencies
├── docker-compose.yml        # Docker services
├── Dockerfile               # Application Docker image
└── composer.json            # PHP dependencies
```

Each module (Account and Transfer) is self-contained with its own domain logic, application services, infrastructure, and presentation layers, following the modular monolith pattern.

## 🤝 Contributing

This is a simplified payment gateway implementation. Key areas for improvement:
- Complete Transfer module implementation
- Add Transfer history endpoints
- Implement retry logic for external services
- Add comprehensive test coverage
- Implement rate limiting
- Add authentication/authorization

## 📄 License

Apache-2.0

## 🔗 References

- [Hyperf Documentation](https://hyperf.wiki)
- [Swoole Documentation](https://wiki.swoole.com)
