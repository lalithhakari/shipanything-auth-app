# Auth Microservice

This is the Auth microservice for the ShipAnything platform, serving as the **centralized authentication and authorization gateway** for all protected services. It handles JWT token management, user authentication, and provides token validation for the NGINX API Gateway.

## Features

-   User registration and authentication
-   JWT token management (access tokens, refresh tokens)
-   Centralized authorization for all microservices
-   API Gateway integration with NGINX
-   Token validation for inter-service communication
-   Role-based access control (RBAC)
-   Rate limiting and security features
-   Internal auth validation endpoint for NGINX `auth_request`

## Authentication Endpoints

### Public Endpoints (No Authentication Required)

-   `POST /api/auth/register` - User registration
-   `POST /api/auth/login` - User login
-   `GET /health` - Service health check

### Protected Endpoints (Require Bearer Token)

-   `POST /api/auth/refresh` - Refresh access token
-   `POST /api/auth/logout` - User logout
-   `GET /api/auth/user` - Get authenticated user profile

### Internal Endpoints (Container Network Only)

-   `POST /api/auth/validate-token` - Token validation for NGINX gateway
-   `GET /api/test/dbs` - Database connectivity test
-   `GET /api/test/rabbitmq` - RabbitMQ connectivity test
-   `GET /api/test/kafka` - Kafka connectivity test

## 🔐 **Quick Usage Examples**

### 1. Register a New User

```bash
curl -X POST http://auth.shipanything.test/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword123",
    "password_confirmation": "securepassword123"
  }'
```

### 2. Login and Get Access Token

```bash
curl -X POST http://auth.shipanything.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "securepassword123"
  }'
```

### 3. Access Other Services with Token

```bash
# Use the token to access protected services
curl -X GET http://location.shipanything.test/api/locations \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## 🏗️ **API Gateway Integration**

This service acts as the authentication provider for the NGINX API Gateway:

```
Client Request → NGINX Gateway → Auth Service (validate token) → Target Service
```

When you make requests to protected services (location, payments, booking, fraud), NGINX automatically:

1. Intercepts the request at the gateway level
2. Validates the Bearer token with this auth service via `/api/auth/validate-token`
3. Forwards user context headers (`X-User-ID`, `X-User-Email`) to the target service
4. Returns the response to the client

**Key Integration Points:**

-   **NGINX `auth_request` module**: Uses this service for token validation
-   **Internal validation endpoint**: `/api/auth/validate-token` (container network only)
-   **User context injection**: Provides user information to downstream services
-   **Rate limiting**: Protects auth endpoints from abuse

## Token Security

-   **Access Tokens**: Expire in 15 days
-   **Refresh Tokens**: Expire in 30 days
-   **Personal Access Tokens**: Expire in 6 months
-   **Rate Limiting**: 10 requests/minute for auth endpoints
-   **Secure Storage**: Tokens are stateless JWT with secure signing

## Environment Variables

-   `DB_HOST` - PostgreSQL host (`auth-postgres`)
-   `DB_DATABASE` - Database name (`auth_db`)
-   `DB_USERNAME` - Database user (`auth_user`)
-   `DB_PASSWORD` - Database password (`auth_password`)
-   `REDIS_HOST` - Redis host (`auth-redis`)
-   `RABBITMQ_HOST` - RabbitMQ host (`auth-rabbitmq`)
-   `RABBITMQ_USER` - RabbitMQ user (`auth_user`)
-   `RABBITMQ_PASSWORD` - RabbitMQ password (`auth_password`)
-   `KAFKA_BROKERS` - Kafka brokers list (`kafka:29092`)

## Database Connection (Development)

**PostgreSQL:**

-   Host: `localhost`
-   Port: `5433`
-   Database: `auth_db`
-   Username: `auth_user`
-   Password: `auth_password`

**Redis:**

-   Host: `localhost`
-   Port: `6380`

**RabbitMQ Management UI:**

-   URL: http://localhost:15672
-   Username: `auth_user`
-   Password: `auth_password`

## Docker Compose Ports

-   **Application**: 8081
-   **PostgreSQL**: 5433
-   **Redis**: 6380
-   **RabbitMQ AMQP**: 5672
-   **RabbitMQ Management**: 15672

## Development

This service is part of the larger ShipAnything microservices platform. See the main repository README for setup and deployment instructions.

### Running Commands

```bash
# Navigate to the docker folder
cd microservices/auth-app/docker

# Run artisan commands
./cmd.sh php artisan migrate
./cmd.sh php artisan make:controller UserController
./cmd.sh composer install
```
