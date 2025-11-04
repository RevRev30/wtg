# Quick Reference Guide

## Status Flow

```
┌─────────┐
│ PENDING │  ← Customer creates reservation
└────┬────┘
     │
     │ Staff approves
     ▼
┌─────────┐
│APPROVED │  ← Email sent to customer (doesn't block tables)
└────┬────┘
     │
     │ Customer confirms
     ▼
┌──────────┐
│CONFIRMED │  ← Table is now blocked/held
└────┬─────┘
     │
     │ After event
     ▼
┌──────────┐
│COMPLETED │  ← Table is freed
└──────────┘

     ❌ CANCELLED (can happen from any state before completed)
```

## Routes Quick Reference

### Customer Routes
- `GET /reservations/{id}/customer-confirm` - Confirm approved reservation
- `GET /reservations/{id}/customer-cancel` - Cancel reservation

### Staff Routes
- `GET /staff/reservations` - List all reservations
- `GET /staff/reservations/{id}` - View reservation details
- `PATCH /staff/reservations/{id}/status` - Update status manually
- `POST /staff/reservations/{id}/approve` - Approve & notify customer
- `POST /staff/reservations/{id}/complete` - Mark as completed
- `POST /staff/reservations/{id}/cancel` - Cancel reservation

## Status Rules

| Status | Can Confirm? | Can Cancel? | Blocks Table? | Email Sent? |
|--------|-------------|-------------|---------------|-------------|
| Pending | ❌ | ✅ | ❌ | ❌ |
| Approved | ✅ | ✅ | ❌ | ✅ |
| Confirmed | ❌ | ✅ | ✅ | ❌ |
| Cancelled | ❌ | ❌ | ❌ | ❌ |
| Completed | ❌ | ❌ | ❌ | ❌ |

## Code Snippets

### Check if reservation blocks table
```php
// Method 1: Use the helper method
if ($reservation->isBlockingTable()) {
    // Table is blocked
}

// Method 2: Check status directly
if ($reservation->status === 'confirmed') {
    // Table is blocked
}

// Method 3: Use the scope
$blockingReservations = Reservation::blockingTables()->get();
```

### Get all reservations that block tables
```php
$confirmedReservations = Reservation::where('status', 'confirmed')
    ->where('reservation_date', today())
    ->get();
```

### Update seating logic
```php
// OLD: Don't do this
$unavailableTables = Table::whereHas('reservations', function($q) {
    $q->whereIn('status', ['pending', 'approved', 'confirmed']);
})->get();

// NEW: Only confirmed reservations block tables
$unavailableTables = Table::whereHas('reservations', function($q) {
    $q->where('status', 'confirmed')
      ->where('reservation_date', today());
})->get();
```

### Send approval email manually
```php
use App\Mail\ReservationApproved;
use Illuminate\Support\Facades\Mail;

Mail::to($reservation->customer->email)
    ->send(new ReservationApproved($reservation));
```

### Check user authorization
```php
// In controller
if ($reservation->customer_id !== auth()->id()) {
    abort(403, 'Unauthorized');
}

// In blade template
@if($reservation->customer_id === auth()->id())
    <!-- Show customer actions -->
@endif
```

## Common Issues

### Issue: Route not found
**Solution:**
```bash
php artisan route:clear
php artisan route:list | grep reservations
```

### Issue: Email not sending
**Solution:**
```bash
# Check .env configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io

# Test email
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

### Issue: Middleware error
**Solution:**
- Make sure StaffMiddleware is registered in bootstrap/app.php or app/Http/Kernel.php
- Check user has correct role: `role` column in users table

### Issue: Migration error
**Solution:**
```bash
# If migration fails, run manually in MySQL:
ALTER TABLE reservations 
MODIFY COLUMN status ENUM('pending', 'approved', 'confirmed', 'cancelled', 'completed') 
DEFAULT 'pending';
```

## Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ReservationApprovalWorkflowTest.php

# Run specific test method
php artisan test --filter=staff_can_approve_pending_reservation

# Run with coverage
php artisan test --coverage
```

## Useful Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list --path=reservations

# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback --step=1

# Tinker to test
php artisan tinker
>>> $reservation = Reservation::find(1);
>>> $reservation->status;
>>> $reservation->isBlockingTable();

# Start queue worker (if using queues)
php artisan queue:work

# Monitor queue
php artisan queue:monitor
```

## Database Queries

```sql
-- Check reservation statuses
SELECT status, COUNT(*) as count 
FROM reservations 
GROUP BY status;

-- Find reservations needing confirmation
SELECT * FROM reservations 
WHERE status = 'approved' 
AND updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Find blocking reservations for today
SELECT * FROM reservations 
WHERE status = 'confirmed' 
AND reservation_date = CURDATE();

-- Update status manually
UPDATE reservations 
SET status = 'approved' 
WHERE id = 123;
```

## Email Template Customization

The email template is located at: `resources/views/emails/reservation-approved.blade.php`

To customize:
1. Update the styles in the `<style>` tag
2. Modify the content in the `<body>` section
3. Change button colors by editing `.btn-confirm` and `.btn-cancel` classes
4. Add restaurant logo by adding an `<img>` tag in the header

## Blade Components

Common status badges:
```blade
<span class="px-3 py-1 rounded-full text-sm font-semibold
    @if($reservation->status === 'pending') bg-yellow-100 text-yellow-800
    @elseif($reservation->status === 'approved') bg-blue-100 text-blue-800
    @elseif($reservation->status === 'confirmed') bg-green-100 text-green-800
    @elseif($reservation->status === 'cancelled') bg-red-100 text-red-800
    @elseif($reservation->status === 'completed') bg-gray-100 text-gray-800
    @endif">
    {{ ucfirst($reservation->status) }}
</span>
```

## Security Checklist

- ✅ CSRF protection on all forms
- ✅ Authorization checks (customer owns reservation)
- ✅ Staff middleware for protected routes
- ✅ Input validation on all requests
- ✅ Email verification before sending
- ✅ Secure route parameters (no direct status injection)

## Performance Tips

1. **Use Eager Loading:**
   ```php
   $reservations = Reservation::with(['customer', 'restaurant', 'table'])->get();
   ```

2. **Add Database Indexes:**
   ```php
   Schema::table('reservations', function (Blueprint $table) {
       $table->index(['status', 'reservation_date']);
       $table->index('customer_id');
   });
   ```

3. **Use Queue for Emails:**
   ```php
   Mail::to($email)->queue(new ReservationApproved($reservation));
   ```

4. **Cache Frequently Accessed Data:**
   ```php
   Cache::remember("reservation.{$id}", 3600, function () use ($id) {
       return Reservation::with(['customer', 'restaurant'])->find($id);
   });
   ```
