# Setup Instructions

This guide will help you integrate the reservation approval workflow into your existing Laravel project.

## Prerequisites

- PHP 8.1 or higher
- MySQL database
- Laravel 10.x or higher
- Composer installed
- Mail configuration setup in `.env`

## Step-by-Step Installation

### 1. Copy Files to Your Project

Copy the following files from this repository to your Laravel project:

```
database/migrations/2024_01_01_000001_add_approved_status_to_reservations.php
  → your-project/database/migrations/

app/Http/Controllers/StaffReservationController.php
  → your-project/app/Http/Controllers/

app/Http/Controllers/ReservationController.php
  → your-project/app/Http/Controllers/

app/Mail/ReservationApproved.php
  → your-project/app/Mail/

resources/views/emails/reservation-approved.blade.php
  → your-project/resources/views/emails/

resources/views/staff/reservation-details.blade.php
  → your-project/resources/views/staff/

routes/web.php
  → Merge the routes into your-project/routes/web.php
```

### 2. Run Database Migration

```bash
php artisan migrate
```

This will update your `reservations` table to include the 'approved' status.

### 3. Configure Email

Make sure your `.env` file has mail configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

For testing, you can use [Mailtrap](https://mailtrap.io/) or [MailHog](https://github.com/mailhog/MailHog).

### 4. Update Routes

Merge the routes from `routes/web.php` into your existing routes file:

**Customer Routes:**
```php
Route::middleware('auth')->group(function () {
    // ... existing routes ...
    
    Route::get('/reservations/{reservation}/customer-confirm', [ReservationController::class, 'customerConfirm'])->name('reservations.customer-confirm');
    Route::get('/reservations/{reservation}/customer-cancel', [ReservationController::class, 'customerCancel'])->name('reservations.customer-cancel');
});
```

**Staff Routes:**
```php
Route::middleware(['auth', 'staff'])->prefix('staff')->name('staff.')->group(function () {
    // ... existing routes ...
    
    Route::patch('/reservations/{reservation}/status', [StaffReservationController::class, 'updateStatus'])->name('reservations.updateStatus');
    Route::post('/reservations/{reservation}/approve', [StaffReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation}/complete', [StaffReservationController::class, 'complete'])->name('reservations.complete');
    Route::post('/reservations/{reservation}/cancel', [StaffReservationController::class, 'cancel'])->name('reservations.cancel');
});
```

### 5. Update Your Existing Controllers

If you already have `ReservationController` or `StaffReservationController`:

**Option A:** Merge the new methods into your existing controllers
- Add `customerConfirm()` and `customerCancel()` to `ReservationController`
- Add `updateStatus()`, `approve()`, `complete()`, and `cancel()` to `StaffReservationController`

**Option B:** Replace your controllers with the new ones (backup first!)

### 6. Update Seating Logic

In your seating/table assignment logic, update to treat 'approved' like 'pending':

```php
// Old logic:
$reservation->status === 'confirmed'

// New logic:
in_array($reservation->status, ['confirmed'])
// or
$reservation->status === 'confirmed'

// Make sure 'approved' doesn't block tables
$blockedStatuses = ['confirmed']; // not 'approved' or 'pending'
```

### 7. Clear Caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 8. Test the Workflow

1. Create a test reservation (status: pending)
2. Login as staff and navigate to reservation details
3. Click "Approve & Notify Customer"
4. Check that email is sent (check Mailtrap/MailHog)
5. Click the confirm link in the email
6. Verify status changes to 'confirmed'

## Troubleshooting

### Route Not Found Error

Make sure you've cleared the route cache:
```bash
php artisan route:clear
php artisan route:list | grep reservations
```

### Email Not Sending

Check your `.env` mail configuration and test with:
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

### Migration Error

If you get an error about the column already existing, you can manually update the enum:

```sql
ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'approved', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending';
```

### Middleware Error

Make sure you have a 'staff' middleware. If not, create it:

```bash
php artisan make:middleware StaffMiddleware
```

And register it in `app/Http/Kernel.php`:
```php
protected $middlewareAliases = [
    // ... other middleware
    'staff' => \App\Http\Middleware\StaffMiddleware::class,
];
```

## Features

### Status Flow
1. **Pending** → Customer creates reservation
2. **Approved** → Staff approves, email sent to customer
3. **Confirmed** → Customer confirms via email link (table is secured)
4. **Completed** → Staff marks as done after the event
5. **Cancelled** → Can be cancelled by staff or customer before completion

### Key Points
- Only `confirmed` reservations block tables
- `approved` reservations don't hold tables until customer confirms
- Email contains secure links for customer actions
- Staff can manually change status or use quick action buttons

## Optional Enhancements

### 24-Hour Auto-Cancellation

To auto-cancel `approved` reservations that aren't confirmed within 24 hours, create a scheduled task:

```bash
php artisan make:command CancelUnconfirmedReservations
```

In the command:
```php
public function handle()
{
    $cutoff = now()->subHours(24);
    
    Reservation::where('status', 'approved')
        ->where('updated_at', '<', $cutoff)
        ->update(['status' => 'cancelled']);
        
    $this->info('Cancelled unconfirmed reservations');
}
```

Register in `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('reservations:cancel-unconfirmed')->hourly();
}
```

## Support

If you encounter issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Route list: `php artisan route:list`
3. Database structure: `php artisan migrate:status`
4. Mail test: `php artisan tinker` → send test email
