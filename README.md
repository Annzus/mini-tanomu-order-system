# mini-tanomu-order-system
A small Laravel 11 + Vue 3 demo for B2B wholesale order management.

## Project Structure

```text
mini-tanomu-order-system/
  backend/   Laravel 11 API application
  frontend/  Vue 3 + TypeScript + Vite application
  docs/      Planning and development notes
```

## Current Status

The project skeleton is in place:

- `backend/` contains the Laravel 11 application skeleton from the official `laravel/laravel` `11.x` branch.
- `frontend/` contains the Vue 3 TypeScript application skeleton with Vue Router, Pinia, and Axios.
- `docs/development-steps.md` tracks the implementation steps from the specification.

PHP and Composer are required before backend dependencies can be installed.

## Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

## Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The backend setup commands require PHP 8.3 and Composer.
