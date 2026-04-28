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

```bash
composer install
