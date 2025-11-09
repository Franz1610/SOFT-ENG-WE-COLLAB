# Booking Reminder System

This system automatically sends email notifications to users when they have 15 minutes left in their booking session.

## Features

- Automatically checks for bookings ending in 15 minutes
- Sends email notifications to users
- Prevents duplicate reminders
- Logs all reminder activities
- Only sends reminders for approved bookings

## Files Created/Modified

### 1. Notification Class
- `app/Notifications/BookingReminder.php` - Email notification template

### 2. Console Command
- `app/Console/Commands/SendBookingReminders.php` - Command to check and send reminders

### 3. Test Command
- `app/Console/Commands/TestBookingReminder.php` - Create test bookings for testing

### 4. Database Migration
- Added `reminder_sent_at` field to bookings table to prevent duplicates

### 5. Scheduler
- Added to `routes/console.php` to run every minute

### 6. Model Updates
- Updated `Booking.php` model to include new `reminder_sent_at` field

## How It Works

1. **Scheduler runs every minute** - The Laravel scheduler runs the reminder command every minute
2. **Command checks for eligible bookings** - Finds bookings that:
   - Are scheduled for today
   - Have status "approved" 
   - End time is 14-16 minutes from now (2-minute window)
   - Haven't received a reminder yet (`reminder_sent_at` is null)
3. **Sends email notification** - Uses Laravel's notification system to send email
4. **Marks as sent** - Updates `reminder_sent_at` to prevent duplicate reminders
5. **Logs activity** - Records all actions for monitoring

## Usage

### Manual Command Execution
```bash
php artisan bookings:send-reminders
```

### Testing the System
1. Create a test booking:
```bash
php artisan bookings:test-reminder {user_id}
```

2. Then immediately run the reminder command:
```bash
php artisan bookings:send-reminders
```

### Setting Up the Scheduler
The scheduler is already configured in `routes/console.php`. To make it work in production, you need to add this to your server's cron tab:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Email Content

The reminder email includes:
- Personalized greeting with user's first name
- Remaining time in minutes
- Booking details (room, date, time)
- Friendly reminder to wrap up activities

## Configuration

### Email Settings
Make sure your email configuration is set up in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email
MAIL_FROM_NAME="Your App Name"
```

For testing, you can use:
```env
MAIL_MAILER=log
```
This will write emails to the log files instead of sending them.

## Monitoring

Check the Laravel logs for reminder activities:
```bash
tail -f storage/logs/laravel.log
```

## Customization

### Changing the Reminder Time
To change from 15 minutes to a different time, modify the time check in:
`app/Console/Commands/SendBookingReminders.php`

### Customizing Email Content
Modify the email template in:
`app/Notifications/BookingReminder.php`

### Changing Schedule Frequency
Modify the schedule in:
`routes/console.php`

Available options: `everyMinute()`, `everyFiveMinutes()`, `everyTenMinutes()`, etc.