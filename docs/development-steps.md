# Development Steps

This document tracks the implementation order for the Mini B2B Order Management System.
It is based on section 18, "Implementation Order", from the project specification.

Last updated: 2026-06-02

## Current Snapshot

- Backend skeleton exists under `backend/`.
- Backend business migrations, Eloquent models, relationships, and demo seed data have been added.
- Frontend skeleton exists under `frontend/`.
- Frontend has Vue Router, Pinia, Axios API client, auth store, route guards, and Japanese UI page scaffolds.
- Frontend production build has been verified with `npm run build`.
- PHP 8.3 and Composer are installed and backend Composer dependencies have been installed.
- MySQL 8.4 is installed and the `mini_tanomu_order_system` database has been created.
- Laravel Sanctum config and personal access token migration have been published.
- Backend migrations and demo seed data have been run successfully.
- Backend auth APIs have been implemented and verified with the seeded Customer A and Admin accounts.
- Backend auth behavior is covered by Feature tests.
- Customer product APIs have been implemented and verified with the seeded Customer A account.
- The customer product page reads products and customer-specific prices from the backend.
- Order, admin, and CSV APIs are still pending.

## Final Goal

Build a small but complete B2B wholesale order management demo using Laravel 11 and Vue 3.

The completed system should allow:

- Customer users to log in, view products, see customer-specific prices, create orders, and view their own order history.
- Admin users to log in, view all orders, update order statuses, and export order data as CSV.
- The backend to calculate prices, create orders inside a DB transaction, and store order item price snapshots.
- The README to clearly explain the business purpose, setup, demo accounts, database design, and key implementation points.

## Step 1: Backend Project Setup

Status: Complete

Tasks:

- [x] Create Laravel 11 project under `backend/`.
- [x] Configure MySQL connection defaults in `backend/.env.example`.
- [x] Add Laravel Sanctum dependency declaration to `backend/composer.json`.
- [x] Add initial API route entrypoint at `backend/routes/api.php`.
- [x] Install backend Composer dependencies.
- [x] Publish/configure Laravel Sanctum.
- [x] Create database migrations.
- [x] Create Eloquent models.
- [x] Define model relationships.
- [x] Create seeders for demo data.

Expected output:

- [x] `backend/` Laravel application exists.
- [x] `.env.example` exists for backend setup.
- [x] Database schema can be migrated.
- [x] Demo data can be seeded.

Notes:

- PHP 8.3 and Composer are available.
- MySQL 8.4 is running locally as a background process because Windows service installation requires elevated permissions.

## Step 2: Auth

Status: Complete

Tasks:

- [x] Implement login API.
- [x] Implement logout API.
- [x] Implement current-user API.
- [x] Implement role middleware for `customer` and `admin`.

Expected APIs:

- `POST /api/login`
- `POST /api/logout`
- `GET /api/me`

Expected output:

- [x] Demo users can log in with Sanctum token authentication.
- [x] Authenticated users can fetch current-user data.
- [x] Authenticated users can log out and revoke the current token.
- [x] Role middleware is registered for future customer/admin routes.
- [x] Invalid login attempts return validation errors.
- [x] Unauthenticated API requests return JSON 401 responses.

Notes:

- Role rejection behavior will be exercised when Step 3 and Step 5 add customer/admin protected routes.
- Auth API tests cover customer login, admin login, invalid credentials, logout, and unauthenticated JSON responses.

## Step 3: Product API

Status: Complete

Tasks:

- [x] Implement customer product list API.
- [x] Implement customer-specific price resolution.
- [x] Implement `ProductResource`.
- [x] Return only active products to customers.

Expected APIs:

- `GET /api/products`
- `GET /api/products/{id}`

Expected output:

- [x] Customer users can view active products.
- [x] Product response includes resolved price for the current customer.
- [x] Backend resolves prices; frontend does not calculate customer-specific prices.
- [x] Admin users cannot access the customer product API.
- [x] Inactive products are hidden from list and detail responses.

Notes:

- Product API tests cover customer price resolution, inactive products, admin rejection, and unauthenticated rejection.
- Demo customer, user, product, and unit names are seeded in Japanese for the target market.

## Step 4: Order API

Status: Not started

Tasks:

- [ ] Implement `StoreOrderRequest`.
- [ ] Implement `OrderService`.
- [ ] Implement order creation inside a DB transaction.
- [ ] Generate backend order numbers.
- [ ] Store order item product snapshots.
- [ ] Calculate `subtotal` and `total_amount` on the backend.
- [ ] Implement customer order history.
- [ ] Implement customer order detail.
- [ ] Ensure customers can only view their own orders.

Expected APIs:

- `POST /api/orders`
- `GET /api/orders`
- `GET /api/orders/{id}`

Expected output:

- Customer users can create orders.
- Inactive products cannot be ordered.
- Frontend-submitted prices are not accepted or trusted.
- Historical order item prices remain unchanged even if product prices change later.

## Step 5: Admin API

Status: Not started

Tasks:

- [ ] Implement admin order list.
- [ ] Implement admin order detail.
- [ ] Implement order status update.
- [ ] Validate allowed status transitions.
- [ ] Implement CSV export.

Expected APIs:

- `GET /api/admin/orders`
- `GET /api/admin/orders/{id}`
- `PATCH /api/admin/orders/{id}/status`
- `GET /api/admin/orders/export`

Expected output:

- Admin users can view all customer orders.
- Admin users can update order status only through allowed transitions.
- Admin users can export order data as CSV.
- CSV contains one row per order item.

## Step 6: Frontend Setup

Status: Complete

Tasks:

- [x] Create Vue 3 + TypeScript + Vite project under `frontend/`.
- [x] Configure Vue Router.
- [x] Configure Pinia.
- [x] Configure Axios API client.
- [x] Implement auth store.
- [x] Implement route guards and role-based redirects.
- [x] Add `frontend/.env.example`.
- [x] Verify production build with `npm run build`.

Expected output:

- [x] `frontend/` Vue application exists.
- [x] Frontend can call backend APIs through a shared API client.
- [x] Login state is stored and restored for the demo.

## Step 7: Frontend Pages

Status: In progress

Tasks:

- [x] Implement login page scaffold.
- [x] Implement customer product list page scaffold.
- [x] Implement customer order history page scaffold.
- [x] Implement customer order detail page scaffold.
- [x] Implement admin order list page scaffold.
- [x] Implement admin order detail page scaffold.
- [x] Convert visible UI copy to Japanese.
- [x] Wire customer product page to backend Product API.
- [ ] Wire pages to backend APIs.
- [ ] Implement real cart state and order submission.
- [ ] Add complete loading states.
- [ ] Add complete error handling.

Expected routes:

- `/login`
- `/customer/products`
- `/customer/orders`
- `/customer/orders/:id`
- `/admin/orders`
- `/admin/orders/:id`

Expected output:

- [ ] Customer users can complete the product browsing and order creation flow.
- [ ] Customer users can view their own order history and details.
- [ ] Admin users can view, filter, update, and export orders.

## Step 8: README And Cleanup

Status: In progress

Tasks:

- [x] Add initial setup instructions.
- [x] Ensure `.env` is ignored.
- [x] Ensure backend and frontend `.env.example` files exist.
- [x] Maintain root `.gitignore` for monorepo structure.
- [ ] Add demo account information.
- [ ] Add database design summary.
- [ ] Add key implementation points.
- [ ] Add screenshots if available.
- [ ] Remove unused template code.

Expected output:

- [ ] README explains the project clearly for a Laravel 11 / Vue 3 B2B SaaS demo.
- [ ] Repository is clean and ready to share.

## Acceptance Checklist

- [x] Customer A can log in.
- [x] Customer A can see product list.
- [x] Customer A can see customer-specific prices.
- [ ] Customer A can create an order.
- [ ] Customer A can view own order history.
- [ ] Customer A cannot view Customer B's order.
- [ ] Admin can log in.
- [ ] Admin can see all orders.
- [ ] Admin can update order status.
- [ ] Admin can export orders as CSV.
- [ ] Order creation uses DB transaction.
- [ ] Order items store price snapshots.
- [ ] README explains the business purpose and implementation points.

## Deferred Items

Do not implement these in v1 unless the core flow is already complete:

- Product management.
- Customer management.
- Order status history.
- Product search.
- Product categories.
- Admin dashboard summary.
- Docker Compose.
- Redis or Valkey.
- AWS deployment.
- Email, LINE, FAX-OCR, payment, or real inventory reservation.
