# Integration Checklist

Use this checklist to ensure you've properly integrated the reservation approval workflow into your Laravel project.

## Pre-Integration

- [ ] Read IMPLEMENTATION_SUMMARY.md to understand the system
- [ ] Review ARCHITECTURE.md to understand the flow
- [ ] Backup your existing database
- [ ] Backup your existing code (especially routes and controllers)
- [ ] Ensure you're using Laravel 10+ with PHP 8.1+

## File Copy

### Required Files (Must Copy)

- [ ] Copy `database/migrations/2024_01_01_000001_add_approved_status_to_reservations.php`
  - To: `your-project/database/migrations/`
  
- [ ] Copy `app/Mail/ReservationApproved.php`
  - To: `your-project/app/Mail/`
  
- [ ] Copy `resources/views/emails/reservation-approved.blade.php`
  - To: `your-project/resources/views/emails/`

### Controllers (Merge or Replace)

- [ ] Option A: Merge methods from `app/Http/Controllers/StaffReservationController.php` into your existing controller
- [ ] Option B: Replace your `StaffReservationController.php` entirely (backup first!)

- [ ] Option A: Merge methods from `app/Http/Controllers/ReservationController.php` into your existing controller  
- [ ] Option B: Replace your `ReservationController.php` entirely (backup first!)

### Models (Merge or Replace)

- [ ] Option A: Add methods from `app/Models/Reservation.php` to your existing model
- [ ] Option B: Replace your Reservation model (backup first!)

### Middleware

- [ ] Copy `app/Http/Middleware/StaffMiddleware.php`
  - To: `your-project/app/Http/Middleware/`
- [ ] Register middleware (see Configuration section below)

### Routes

- [ ] Open `routes/web.php` from this repository
- [ ] Merge the customer routes into your `routes/web.php`
- [ ] Merge the staff routes into your `routes/web.php`
- [ ] DO NOT duplicate existing routes

### Views

- [ ] Copy `resources/views/staff/reservation-details.blade.php`
  - To: `your-project/resources/views/staff/`
- [ ] Ensure you have a `resources/views/layouts/staff.blade.php` layout
- [ ] If not, create one (see CONFIGURATION.md)

## Configuration

### Register Middleware

- [ ] Laravel 11: Add to `bootstrap/app.php`
  ```php
  $middleware->alias([
      'staff' => \App\Http\Middleware\StaffMiddleware::class,
  ]);
  ```

- [ ] Laravel 10: Add to `app/Http/Kernel.php`
  ```php
  protected $middlewareAliases = [
      'staff' => \App\Http\Middleware\StaffMiddleware::class,
  ];
  ```

### Environment Configuration

- [ ] Update `.env` with mail configuration:
  ```
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.mailtrap.io
  MAIL_PORT=2525
  MAIL_USERNAME=your_username
  MAIL_PASSWORD=your_password
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS=noreply@example.com
  MAIL_FROM_NAME="${APP_NAME}"
  ```

### Database

- [ ] Run migration: `php artisan migrate`
- [ ] Verify migration succeeded
- [ ] Check that 'approved' status was added to reservations table

### Clear Caches

- [ ] `php artisan config:clear`
- [ ] `php artisan route:clear`
- [ ] `php artisan view:clear`
- [ ] `php artisan cache:clear`

### Verify Routes

- [ ] Run: `php artisan route:list | grep reservations`
- [ ] Verify these routes exist:
  - [ ] `GET /reservations/{reservation}/customer-confirm`
  - [ ] `GET /reservations/{reservation}/customer-cancel`
  - [ ] `PATCH /staff/reservations/{reservation}/status`
  - [ ] `POST /staff/reservations/{reservation}/approve`
  - [ ] `POST /staff/reservations/{reservation}/complete`
  - [ ] `POST /staff/reservations/{reservation}/cancel`

## Code Updates

### Update Seating Logic

- [ ] Find where you check for available tables
- [ ] Update to only consider 'confirmed' reservations as blocking
- [ ] Remove 'pending' and 'approved' from table-blocking logic

**Before:**
```php
$blockedStatuses = ['pending', 'confirmed'];
```

**After:**
```php
$blockedStatuses = ['confirmed'];
```

### Update Views (If Needed)

- [ ] Update reservation list views to show new status
- [ ] Add color coding for 'approved' status (blue)
- [ ] Update any status dropdowns to include 'approved'

## Testing

### Manual Testing

#### Test 1: Create Reservation
- [ ] Login as customer
- [ ] Create a new reservation
- [ ] Verify status is 'pending'
- [ ] Verify reservation appears in list

#### Test 2: Staff Approval
- [ ] Login as staff
- [ ] Navigate to pending reservation
- [ ] Click "Approve & Notify Customer"
- [ ] Verify status changes to 'approved'
- [ ] Verify success message appears

#### Test 3: Email Sent
- [ ] Check mail service (Mailtrap/MailHog)
- [ ] Verify email was sent to customer
- [ ] Verify email contains confirm and cancel links
- [ ] Verify email styling looks correct

#### Test 4: Customer Confirmation
- [ ] Login as customer (or use email link)
- [ ] Click confirm link in email
- [ ] Verify status changes to 'confirmed'
- [ ] Verify success message appears
- [ ] Verify table is now blocked (if assigned)

#### Test 5: Complete Reservation
- [ ] Login as staff
- [ ] Navigate to confirmed reservation
- [ ] Click "Mark as Completed"
- [ ] Verify status changes to 'completed'
- [ ] Verify table is freed

#### Test 6: Cancellation
- [ ] Create and approve a reservation
- [ ] Click cancel link in email
- [ ] Verify status changes to 'cancelled'
- [ ] Verify table is freed (if assigned)

### Authorization Testing

- [ ] Test that customer cannot access staff routes
- [ ] Test that customer can only confirm their own reservations
- [ ] Test that unauthenticated users are redirected to login
- [ ] Test that staff can access all staff routes

### Edge Cases

- [ ] Try to approve non-pending reservation (should show error)
- [ ] Try to confirm non-approved reservation (should show error)
- [ ] Try to complete non-confirmed reservation (should show error)
- [ ] Try to cancel completed reservation (should show error)
- [ ] Try to access another customer's reservation (should be forbidden)

### Automated Testing (Optional)

- [ ] Copy test file to `tests/Feature/`
- [ ] Run: `php artisan test tests/Feature/ReservationApprovalWorkflowTest.php`
- [ ] Verify all tests pass

## Production Preparation

### Security Review

- [ ] Verify CSRF protection is enabled
- [ ] Check that all routes have proper middleware
- [ ] Ensure email links expire or have tokens
- [ ] Review authorization checks in controllers
- [ ] Test with different user roles

### Performance

- [ ] Add database indexes:
  ```php
  Schema::table('reservations', function (Blueprint $table) {
      $table->index(['status', 'reservation_date']);
  });
  ```
- [ ] Configure queue for emails (recommended)
- [ ] Test email sending performance
- [ ] Enable caching if needed

### Email Configuration

- [ ] Switch from test mail service to production service
  - [ ] SendGrid, Mailgun, Amazon SES, etc.
- [ ] Test email deliverability
- [ ] Configure SPF/DKIM records
- [ ] Test spam score
- [ ] Add unsubscribe link (if required)

### Monitoring

- [ ] Set up logging for approval actions
- [ ] Monitor email delivery failures
- [ ] Track reservation confirmation rates
- [ ] Set up alerts for errors

## Documentation

- [ ] Document the new workflow for your team
- [ ] Update user guides
- [ ] Create staff training materials
- [ ] Document email templates for marketing team

## Rollback Plan

If something goes wrong:

- [ ] Have database backup ready
- [ ] Have code backup ready
- [ ] Know how to rollback migration:
  ```bash
  php artisan migrate:rollback --step=1
  ```
- [ ] Test rollback procedure in staging first

## Post-Integration

### Verify Everything Works

- [ ] All tests pass
- [ ] No errors in logs
- [ ] Emails are being sent
- [ ] Customers can confirm reservations
- [ ] Staff can manage reservations
- [ ] Tables are blocked correctly
- [ ] Status transitions work properly

### Monitor

First 24 hours:
- [ ] Check error logs every hour
- [ ] Monitor email delivery rate
- [ ] Watch for failed jobs (if using queues)
- [ ] Check database for unexpected data

First week:
- [ ] Review approval → confirmation rate
- [ ] Check for any customer confusion
- [ ] Gather staff feedback
- [ ] Optimize based on usage patterns

### Optimization

After one week:
- [ ] Review slow queries
- [ ] Check email open rates
- [ ] Analyze confirmation times
- [ ] Consider adding reminders for unconfirmed

## Support Resources

If you run into issues:

1. **Check Documentation**
   - README.md for overview
   - SETUP.md for installation
   - QUICK_REFERENCE.md for troubleshooting
   - CONFIGURATION.md for setup details

2. **Common Issues**
   - Route not found → Clear route cache
   - Email not sending → Check .env configuration
   - Middleware error → Verify registration
   - Migration failed → Check database permissions

3. **Debug Tools**
   ```bash
   # Check routes
   php artisan route:list
   
   # Check config
   php artisan config:show mail
   
   # Check logs
   tail -f storage/logs/laravel.log
   
   # Test in tinker
   php artisan tinker
   ```

## Sign-Off

When everything is working:

- [ ] All checklist items completed
- [ ] Team trained on new workflow
- [ ] Documentation updated
- [ ] Monitoring in place
- [ ] Rollback plan tested
- [ ] Production deployment approved

**Integration completed by:** ________________

**Date:** ________________

**Verified by:** ________________

**Notes:**
_____________________________________________
_____________________________________________
_____________________________________________

---

**Need help?** Refer to the documentation files or review the test file for examples of how everything should work.
