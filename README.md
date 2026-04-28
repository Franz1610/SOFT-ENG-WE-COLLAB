# CollabSpace

CollabSpace is a Laravel 12 application with Inertia.js and Vue 3 for room booking, booking approvals, finance tracking, feedback collection, and admin operations.

## What it does

- Public landing page with live feedback highlights
- Booking flow for authenticated users
- Booking history, cancellation, payments, and receipt downloads
- Admin and finance tools for managing bookings, rooms, users, payments, and transactions
- Feedback submission and moderation
- Booking reminder emails for upcoming sessions
- Public pages for news and promotions

## Tech Stack

- Laravel 12
- Inertia.js
- Vue 3
- Vite
- Tailwind CSS
- Bootstrap 5
- Dompdf for PDF receipt generation
- Laravel notifications and queued jobs

## Main Routes

- `/` - public home page
- `/whats-new` - updates page
- `/deals` - deals and promo page
- `/booking` - booking interface for logged-in users
- `/booking/history` - booking history
- `/booking/{id}/pay` - payment page and proof upload
- `/booking/{id}/receipt` - receipt download
- `/admin/bookings` - booking management
- `/admin/rooms` - room management
- `/admin/payments` - payment review
- `/admin/finance` - finance dashboard
- `/admin/users` - user management
- `/admin/user-roles` - role management
- `/feedback/create` - feedback submission

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js 20+ recommended
- A database configured in `.env`

## Setup

1. Install PHP dependencies.
`composer install`
2. Install frontend dependencies.
`npm install`
3. Copy the environment file.
`copy .env.example .env`
4. Generate the application key.
`php artisan key:generate`
5. Configure your database and other environment values in `.env.`

APP_NAME=CollabSpace
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=log

6. Run the database migrations.
`php artisan migrate`
7. Start the development servers.
`composer run dev`
