# Mini Tanomu Order System

[English](README.md) | [日本語](README.ja.md)

A full-stack B2B wholesale order management demo built with Laravel 11, Vue 3,
TypeScript, Vite, MySQL, and Laravel Sanctum.

This project demonstrates a role-based ordering workflow: customer users can
browse products, place orders, and view order history, while admin users can
manage orders, update statuses, and export CSV data. The backend handles
authentication, customer-specific pricing, transactional order creation, and
order item price snapshots.

## Features

Customer users can:

- Log in with token authentication.
- View active products.
- See customer-specific prices resolved by the backend.
- Create orders from the product list.
- View their own order history and order details.

Admin users can:

- Log in with token authentication.
- View all customer orders.
- Filter orders by status.
- View order details and item price snapshots.
- Update order status through allowed transitions.
- Export order data as CSV, with one row per order item.

The backend owns all business-critical calculations:

- The frontend never submits trusted prices.
- Order creation runs inside a database transaction.
- Order item rows store product code, name, unit, unit price, quantity, and
  subtotal snapshots.

## Project Structure

```text
mini-tanomu-order-system/
  backend/   Laravel 11 API application
  frontend/  Vue 3 + TypeScript + Vite application
  docs/      Development steps and project notes
```

Important backend locations:

```text
backend/routes/api.php
backend/app/Http/Controllers/Api
backend/app/Http/Requests
backend/app/Http/Resources
backend/app/Models
backend/app/Services
backend/database/migrations
backend/database/seeders/DatabaseSeeder.php
backend/tests/Feature
```

Important frontend locations:

```text
frontend/src/api
frontend/src/components
frontend/src/pages
frontend/src/router
frontend/src/stores
frontend/src/types
frontend/src/style.css
```

## Requirements

- PHP 8.3
- Composer
- MySQL 8.x
- Node.js and npm

The local development database used by this project is:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_tanomu_order_system
DB_USERNAME=root
DB_PASSWORD=
```

## Backend Setup

From the repository root:

```powershell
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

If `php` is not in `PATH` on this Windows machine, use the installed PHP path:

```powershell
& "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan serve --host=127.0.0.1 --port=8000
```

Backend health check:

```text
http://127.0.0.1:8000/api/health
```

## Frontend Setup

Open a second terminal:

```powershell
cd frontend
npm install
npm run dev -- --host 127.0.0.1 --port 5173
```

Frontend URL:

```text
http://127.0.0.1:5173/login
```

The frontend API base URL is configured in:

```text
frontend/.env.example
VITE_API_BASE_URL=http://localhost:8000/api
```

## Demo Accounts

```text
Admin
Email: admin@example.com
Password: password

Customer A
Email: customer-a@example.com
Password: password

Customer B
Email: customer-b@example.com
Password: password
```

## Database Design

Core tables:

- `users`: login users with `admin` or `customer` roles.
- `customers`: customer master data.
- `products`: product master data, default prices, stock quantities, and active
  flags.
- `customer_product_prices`: customer-specific product prices.
- `orders`: order headers, customer, status, desired delivery date, note, total
  amount, and ordered timestamp.
- `order_items`: order line snapshots with product code, product name, unit,
  unit price, quantity, and subtotal.
- `personal_access_tokens`: Laravel Sanctum API tokens.

Demo data is seeded from:

```text
backend/database/seeders/DatabaseSeeder.php
```

## API Summary

Auth:

```text
POST /api/login
POST /api/logout
GET  /api/me
```

Customer:

```text
GET  /api/products
GET  /api/products/{product}
POST /api/orders
GET  /api/orders
GET  /api/orders/{order}
```

Admin:

```text
GET   /api/admin/orders
GET   /api/admin/orders/{order}
PATCH /api/admin/orders/{order}/status
GET   /api/admin/orders/export
```

## Order Status Flow

Allowed admin transitions:

```text
pending   -> confirmed | cancelled
confirmed -> preparing | cancelled
preparing -> delivered
delivered -> final
cancelled -> final
```

Invalid transitions return validation errors.

## Testing

Backend:

```powershell
cd backend
php artisan test
```

Frontend:

```powershell
cd frontend
npm run build
```

Current covered backend areas:

- Authentication API
- Product API and customer-specific prices
- Customer order creation, history, detail, and customer isolation
- Backend price calculation and order item snapshots
- Admin order list, detail, status updates, and CSV export

## Current Scope

Implemented:

- Customer ordering flow
- Admin order management flow
- CSV export
- Japanese visible UI copy for the demo application
- MySQL-backed demo data

Deferred for v1:

- Product management UI
- Customer management UI
- Order status history
- Product search and categories
- Admin dashboard summary
- Docker Compose
- Redis or Valkey
- Deployment
- Email, LINE, FAX-OCR, payment, or real inventory reservation
