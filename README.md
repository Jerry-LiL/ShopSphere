# ShopSphere

Multi-vendor shop built with Laravel. Customers, vendors and an admin each get their own panel, and there is a small Sanctum API for products and orders.

## Stack

- PHP 8.2+ / Laravel 11
- MySQL (Docker / production), SQLite (tests)
- Blade + Bootstrap 5
- Sanctum for API tokens, queues for webhooks

## Running it

With Docker:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

App runs at `http://localhost:8000`.

Without Docker (set the DB_ values in `.env` first):

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Seeded logins (password `password`): `admin@shopsphere.test`, `vendor1@shopsphere.test`, `customer@shopsphere.test`.

Order webhooks are sent from the queue:

```bash
php artisan queue:work
```

## Tests

```bash
php artisan test
```

Tests run on in-memory SQLite.

## Notes

- Catalog filters: category, store, price range, in stock
- Session cart with AJAX updates and stock checks
- Checkout decrements stock inside a transaction; cancelling puts it back
- Order webhooks signed with HMAC-SHA256, retried from the queue
- API access controlled by Sanctum tokens and policies

## API

- POST /api/v1/auth/login
- POST /api/v1/auth/register
- POST /api/v1/auth/logout
- GET /api/v1/auth/user
- GET /api/v1/products
- GET /api/v1/products/{idOrSlug}
- POST /api/v1/products
- PUT/PATCH /api/v1/products/{id}
- DELETE /api/v1/products/{id}
- GET /api/v1/orders
- GET /api/v1/orders/{id}
- POST /api/v1/orders
- PATCH /api/v1/orders/{id}/status
