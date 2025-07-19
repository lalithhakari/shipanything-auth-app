# Auth Microservice

This is the Auth microservice for the ShipAnything platform, handling authentication, authorization, and identity management.

## Features

-   User authentication and authorization
-   JWT token management
-   Role-based access control
-   Identity verification

## Endpoints

-   `GET /health` - Health check
-   `GET /api/test/dbs` - Database connectivity test
-   `GET /api/test/rabbitmq` - RabbitMQ connectivity test
-   `GET /api/test/kafka` - Kafka connectivity test

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
