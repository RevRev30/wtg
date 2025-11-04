# Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                          User Interfaces                             │
├─────────────────────────────────┬───────────────────────────────────┤
│     Customer Interface          │      Staff Interface              │
│  ┌──────────────────────┐      │   ┌──────────────────────┐       │
│  │ - View Reservations  │      │   │ - Reservation List    │       │
│  │ - Create Reservation │      │   │ - Approve Pending     │       │
│  │ - Confirm (via Email)│      │   │ - View Details        │       │
│  │ - Cancel Reservation │      │   │ - Update Status       │       │
│  └──────────────────────┘      │   │ - Mark Completed      │       │
│                                 │   └──────────────────────┘       │
└─────────────────────────────────┴───────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│                          Routes Layer                                │
├─────────────────────────────────┬───────────────────────────────────┤
│   Customer Routes               │      Staff Routes                 │
│   /reservations/*               │      /staff/reservations/*        │
│   (auth middleware)             │      (auth + staff middleware)    │
└─────────────────────────────────┴───────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│                       Controllers Layer                              │
├─────────────────────────────────┬───────────────────────────────────┤
│   ReservationController         │   StaffReservationController      │
│   - index()                     │   - index()                       │
│   - show()                      │   - show()                        │
│   - create()                    │   - approve() ─────┐              │
│   - store()                     │   - updateStatus()  │              │
│   - customerConfirm()           │   - complete()      │              │
│   - customerCancel()            │   - cancel()        │              │
└─────────────────────────────────┴────────────────────┼──────────────┘
                              ↓                         │
┌─────────────────────────────────────────────────────┼──────────────┐
│                        Models Layer                  ↓              │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  Reservation Model                                            │ │
│  │  - Relationships (customer, restaurant, table)                │ │
│  │  - Helper Methods (isBlockingTable())                         │ │
│  │  - Scopes (blockingTables())                                  │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  User Model                           Restaurant Model        │ │
│  │  - role: customer/staff/admin         - name, location        │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  Table Model                                                  │ │
│  │  - name, capacity, status                                     │ │
│  └──────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│                      Mail System                                     │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  ReservationApproved Mailable                                 │ │
│  │  - To: Customer Email                                         │ │
│  │  - Template: reservation-approved.blade.php                   │ │
│  │  - Links: Confirm & Cancel                                    │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                              ↓                                       │
│                       [Email Service]                                │
│                    (SMTP/Mailtrap/etc.)                              │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│                        Database Layer                                │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  reservations                                                 │ │
│  │  - id, customer_id, restaurant_id, table_id                   │ │
│  │  - reservation_date, reservation_time, number_of_guests       │ │
│  │  - status: [pending, approved, confirmed, cancelled,          │ │
│  │              completed]                                        │ │
│  │  - special_requests, timestamps                               │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐            │
│  │   users      │  │ restaurants  │  │   tables     │            │
│  │              │  │              │  │              │            │
│  └──────────────┘  └──────────────┘  └──────────────┘            │
└─────────────────────────────────────────────────────────────────────┘
```

## Data Flow: Approval Workflow

```
1. Customer Creates Reservation
   ┌─────────────────┐
   │ Customer fills  │
   │ reservation     │
   │ form            │
   └────────┬────────┘
            │
            ▼
   ┌─────────────────────┐
   │ POST /reservations  │
   └────────┬────────────┘
            │
            ▼
   ┌──────────────────────────────┐
   │ ReservationController::store │
   └────────┬─────────────────────┘
            │
            ▼
   ┌──────────────────────┐
   │ Save to Database     │
   │ status = 'pending'   │
   └──────────────────────┘

2. Staff Approves Reservation
   ┌─────────────────────────────────┐
   │ Staff views reservation         │
   │ Clicks "Approve & Notify"       │
   └────────┬────────────────────────┘
            │
            ▼
   ┌─────────────────────────────────────────────┐
   │ POST /staff/reservations/{id}/approve       │
   └────────┬────────────────────────────────────┘
            │
            ▼
   ┌──────────────────────────────────────────┐
   │ StaffReservationController::approve      │
   └────────┬─────────────────────────────────┘
            │
            ├──────────────────────┐
            ▼                      ▼
   ┌──────────────────┐   ┌──────────────────────────┐
   │ Update Database  │   │ Send Email               │
   │ status='approved'│   │ ReservationApproved Mail │
   └──────────────────┘   └────────┬─────────────────┘
                                   │
                                   ▼
                          ┌────────────────────┐
                          │ Email with links:  │
                          │ - Confirm          │
                          │ - Cancel           │
                          └────────────────────┘

3. Customer Confirms
   ┌─────────────────────────┐
   │ Customer receives email │
   │ Clicks "Confirm"        │
   └────────┬────────────────┘
            │
            ▼
   ┌────────────────────────────────────────────┐
   │ GET /reservations/{id}/customer-confirm    │
   └────────┬───────────────────────────────────┘
            │
            ▼
   ┌──────────────────────────────────────────┐
   │ ReservationController::customerConfirm   │
   └────────┬─────────────────────────────────┘
            │
            ├──────────────────────┐
            ▼                      ▼
   ┌──────────────────┐   ┌──────────────────┐
   │ Update Database  │   │ Block Table      │
   │ status='confirmed│   │ (if assigned)    │
   └──────────────────┘   └──────────────────┘

4. Complete Reservation
   ┌──────────────────────┐
   │ After dining event   │
   │ Staff marks complete │
   └────────┬─────────────┘
            │
            ▼
   ┌────────────────────────────────────────────┐
   │ POST /staff/reservations/{id}/complete     │
   └────────┬───────────────────────────────────┘
            │
            ▼
   ┌──────────────────────────────────────────┐
   │ StaffReservationController::complete     │
   └────────┬─────────────────────────────────┘
            │
            ├──────────────────────┐
            ▼                      ▼
   ┌──────────────────┐   ┌──────────────────┐
   │ Update Database  │   │ Free Table       │
   │ status='completed│   │ status='available│
   └──────────────────┘   └──────────────────┘
```

## State Machine Diagram

```
                    ┌──────────────┐
                    │   PENDING    │ ◄── Customer creates
                    └──────┬───────┘
                           │
                           │ Staff approves
                           ▼
                    ┌──────────────┐
        ┌──────────►│   APPROVED   │
        │           └──────┬───────┘
        │                  │
        │                  │ Customer confirms
        │                  ▼
        │           ┌──────────────┐
        │           │  CONFIRMED   │ ◄── Table is blocked
        │           └──────┬───────┘
        │                  │
        │                  │ After event
        │                  ▼
        │           ┌──────────────┐
        │           │  COMPLETED   │ ◄── Table is freed
        │           └──────────────┘
        │
        │           ┌──────────────┐
        └───────────│  CANCELLED   │ ◄── Can be cancelled
                    └──────────────┘     from any state
                                         (before completed)

Transitions:
- pending → approved (Staff only)
- approved → confirmed (Customer only)
- confirmed → completed (Staff only)
- any → cancelled (Staff or Customer, except completed)
```

## Security Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Request Flow                            │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
                ┌───────────────────┐
                │ CSRF Verification │
                └─────────┬─────────┘
                          │
                          ▼
                ┌───────────────────┐
                │  Authentication   │ ◄── auth middleware
                │  (Session/Token)  │
                └─────────┬─────────┘
                          │
         ┌────────────────┼────────────────┐
         │                                 │
         ▼ Customer                        ▼ Staff
┌──────────────────┐           ┌──────────────────────┐
│ Check Ownership  │           │ StaffMiddleware      │
│ (Customer ID)    │           │ Check role='staff'   │
└────────┬─────────┘           └──────────┬───────────┘
         │                                 │
         ▼                                 ▼
┌──────────────────┐           ┌──────────────────────┐
│ Customer Routes  │           │   Staff Routes       │
│ - Confirm        │           │   - Approve          │
│ - Cancel Own     │           │   - Update Status    │
└──────────────────┘           │   - Complete         │
                               │   - Cancel Any       │
                               └──────────────────────┘
```

## Database Schema

```
┌──────────────────────────────────────────┐
│             reservations                  │
├──────────────────────────────────────────┤
│ id                  BIGINT (PK)          │
│ customer_id         BIGINT (FK→users)    │
│ restaurant_id       BIGINT (FK→restaurants)
│ table_id            BIGINT (FK→tables)   │
│ reservation_date    DATE                 │
│ reservation_time    TIME                 │
│ number_of_guests    INT                  │
│ special_requests    TEXT                 │
│ status              ENUM(...)            │
│ created_at          TIMESTAMP            │
│ updated_at          TIMESTAMP            │
└──────────────────────────────────────────┘
         │         │           │
         │         │           └──────────┐
         │         │                      │
         ▼         ▼                      ▼
┌──────────┐ ┌─────────────┐    ┌────────────┐
│  users   │ │ restaurants │    │   tables   │
├──────────┤ ├─────────────┤    ├────────────┤
│ id       │ │ id          │    │ id         │
│ name     │ │ name        │    │ name       │
│ email    │ │ location    │    │ capacity   │
│ role     │ │ ...         │    │ status     │
│ ...      │ └─────────────┘    │ ...        │
└──────────┘                    └────────────┘

Indexes:
- reservations.status (for filtering)
- reservations.reservation_date (for date queries)
- reservations.customer_id (for lookups)
- Combined: (status, reservation_date) for common queries
```

## Component Dependencies

```
┌─────────────────────────────────────────────────────────┐
│                    Dependencies                          │
└─────────────────────────────────────────────────────────┘

routes/web.php
    │
    ├─► app/Http/Controllers/ReservationController.php
    │       └─► app/Models/Reservation.php
    │               ├─► app/Models/User.php
    │               ├─► app/Models/Restaurant.php
    │               └─► app/Models/Table.php
    │
    └─► app/Http/Controllers/StaffReservationController.php
            ├─► app/Models/Reservation.php
            └─► app/Mail/ReservationApproved.php
                    └─► resources/views/emails/
                            reservation-approved.blade.php

app/Http/Middleware/StaffMiddleware.php
    └─► Used by routes with 'staff' middleware

database/migrations/
    └─► Modifies reservations table schema

resources/views/staff/reservation-details.blade.php
    └─► Uses routes defined in web.php
```

## Deployment Architecture (Production)

```
┌───────────────────────────────────────────────────────────┐
│                     Load Balancer                          │
└────────────────────┬──────────────────────────────────────┘
                     │
          ┌──────────┼──────────┐
          ▼          ▼          ▼
     ┌────────┐ ┌────────┐ ┌────────┐
     │  Web   │ │  Web   │ │  Web   │
     │ Server │ │ Server │ │ Server │
     └───┬────┘ └───┬────┘ └───┬────┘
         │          │          │
         └──────────┼──────────┘
                    │
         ┌──────────┼──────────┐
         │                     │
         ▼                     ▼
    ┌─────────┐          ┌──────────┐
    │  MySQL  │          │  Redis   │
    │Database │          │  Cache   │
    └─────────┘          └──────────┘
                              │
                              ▼
                         ┌──────────┐
                         │  Queue   │
                         │  Worker  │
                         └────┬─────┘
                              │
                              ▼
                         ┌──────────┐
                         │   Mail   │
                         │ Service  │
                         └──────────┘
```

## Error Handling Flow

```
Request
  │
  ├─► Authentication Failed
  │     └─► Redirect to login
  │
  ├─► Authorization Failed
  │     └─► 403 Forbidden
  │
  ├─► Validation Failed
  │     └─► Redirect back with errors
  │
  ├─► Business Logic Error
  │     └─► Redirect with error message
  │
  ├─► Database Error
  │     └─► Log error + 500 response
  │
  └─► Success
        └─► Redirect with success message
```
