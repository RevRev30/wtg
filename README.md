# WTG - Reservation System with Approval Workflow

This repository contains the implementation of a reservation approval workflow for a restaurant booking system.

## Features Implemented

### 1. Reservation Status Flow
- **Pending**: Initial state when customer creates a reservation
- **Approved**: Staff approves the reservation and notifies the customer
- **Confirmed**: Customer confirms the reservation via email link
- **Cancelled**: Reservation is cancelled by staff or customer
- **Completed**: Reservation is marked as completed after the event

### 2. Approval Workflow
1. Customer creates a reservation → Status: `pending`
2. Staff reviews and clicks "Approve & Notify Customer" → Status: `approved`
3. Customer receives email with confirmation/cancel links
4. Customer clicks "Confirm Reservation" → Status: `confirmed` (table is secured)
5. After the event, staff marks as completed → Status: `completed`

### 3. Email Notifications
- Customers receive an email when their reservation is approved
- Email includes links to confirm or cancel the reservation
- Styled HTML email template with reservation details

### 4. Key Behaviors
- Only `confirmed` reservations hold/block tables in the seating system
- `approved` reservations are treated like `pending` (don't block tables)
- Customers can cancel reservations before they're completed
- Staff can cancel any reservation except completed/cancelled ones

## Files Created

### Database Migration
- `database/migrations/2024_01_01_000001_add_approved_status_to_reservations.php`
  - Adds 'approved' status to the reservations table ENUM

### Controllers
- `app/Http/Controllers/StaffReservationController.php`
  - `updateStatus()`: Update reservation status manually
  - `approve()`: Approve pending reservation and send email
  - `complete()`: Mark confirmed reservation as completed
  - `cancel()`: Cancel a reservation and free the table

- `app/Http/Controllers/ReservationController.php`
  - `customerConfirm()`: Customer confirms approved reservation
  - `customerCancel()`: Customer cancels their reservation

### Mail
- `app/Mail/ReservationApproved.php`
  - Mailable class for sending approval emails

### Views
- `resources/views/emails/reservation-approved.blade.php`
  - HTML email template with confirmation/cancel buttons
  
- `resources/views/staff/reservation-details.blade.php`
  - Staff interface for managing reservation status
  - Quick action buttons for approve, complete, cancel
  - Status update dropdown

### Routes
- `routes/web.php`
  - Customer routes for confirming/canceling reservations
  - Staff routes for managing reservations
  - All routes properly protected with authentication middleware

## Installation

1. Run the migration:
   ```bash
   php artisan migrate
   ```

2. Clear caches:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

## Usage

### For Staff
1. Navigate to the reservation details page
2. Click "Approve & Notify Customer" for pending reservations
3. Customer receives email notification
4. Wait for customer to confirm (status shows "Awaiting customer confirmation")
5. Once confirmed, mark as completed after the event

### For Customers
1. Check email for approval notification
2. Click "Confirm Reservation" to secure the table
3. Click "Cancel" if unable to attend

## Notes
- Approved reservations don't block tables until confirmed
- Only confirmed reservations should be considered for seating assignments
- Email links are authenticated and verify customer ownership
