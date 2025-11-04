# Configuration Guide

## 1. Register Middleware

Add the StaffMiddleware to your `bootstrap/app.php` (Laravel 11) or `app/Http/Kernel.php` (Laravel 10):

### Laravel 11 (bootstrap/app.php)
```php
use App\Http\Middleware\StaffMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'staff' => StaffMiddleware::class,
        ]);
    })
    // ... rest of configuration
```

### Laravel 10 (app/Http/Kernel.php)
```php
protected $middlewareAliases = [
    // ... existing middleware
    'staff' => \App\Http\Middleware\StaffMiddleware::class,
];
```

## 2. Database Schema Requirements

Your `reservations` table should have these columns:

```sql
CREATE TABLE reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    table_id BIGINT UNSIGNED NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    number_of_guests INT NOT NULL,
    special_requests TEXT NULL,
    status ENUM('pending', 'approved', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    FOREIGN KEY (table_id) REFERENCES tables(id)
);
```

## 3. User Model Requirements

Your `User` model should have a `role` column:

```php
// In your User model
protected $fillable = [
    'name',
    'email',
    'password',
    'role', // 'customer', 'staff', or 'admin'
];
```

## 4. Required Relationships

### User Model
```php
public function reservations()
{
    return $this->hasMany(Reservation::class, 'customer_id');
}
```

### Restaurant Model
```php
public function reservations()
{
    return $this->hasMany(Reservation::class);
}

public function tables()
{
    return $this->hasMany(Table::class);
}
```

### Table Model
```php
public function restaurant()
{
    return $this->belongsTo(Restaurant::class);
}

public function reservations()
{
    return $this->hasMany(Reservation::class);
}
```

## 5. Environment Variables

Add these to your `.env` file:

```env
# Application
APP_NAME="Your Restaurant Name"
APP_URL=http://localhost

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourrestaurant.com
MAIL_FROM_NAME="${APP_NAME}"

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 6. Queue Configuration (Optional but Recommended)

For better performance, configure queues for sending emails:

```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:table
php artisan migrate
```

Update the Mailable to use queues:
```php
class ReservationApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    // ... rest of the class
}
```

Start the queue worker:
```bash
php artisan queue:work
```

## 7. Layout File

Make sure you have a staff layout file at `resources/views/layouts/staff.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Staff Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <!-- Your navigation here -->
    </nav>
    
    <main>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif
        
        @yield('content')
    </main>
</body>
</html>
```

## 8. Testing Checklist

- [ ] Migration runs successfully
- [ ] Routes are registered (check with `php artisan route:list`)
- [ ] Staff middleware is working
- [ ] Email configuration is correct (test with Mailtrap)
- [ ] Pending → Approved → Confirmed workflow works
- [ ] Email links work correctly
- [ ] Table blocking only happens for confirmed reservations
- [ ] Cancellation works from both staff and customer side
- [ ] Status badges display correctly in the UI

## 9. Production Checklist

Before deploying to production:

- [ ] Use a proper mail service (SendGrid, Mailgun, SES)
- [ ] Set up queue workers with supervisor
- [ ] Add rate limiting to prevent spam
- [ ] Test email deliverability
- [ ] Add logging for important actions
- [ ] Set up monitoring for failed emails
- [ ] Add tests for the approval workflow
- [ ] Review security (CSRF protection, authorization checks)
- [ ] Add database indexes for performance
- [ ] Configure proper error handling
