# UI Flow Guide

This document describes the user interface flow for the reservation approval workflow.

## 1. Customer Creates Reservation

**Page:** Reservation Create Form

**Fields:**
- Restaurant (dropdown)
- Date (date picker)
- Time (time picker)
- Number of Guests (number input)
- Special Requests (textarea, optional)

**Action:** Customer clicks "Submit Reservation"

**Result:** 
- Reservation created with status: `pending`
- Redirects to reservations list
- Shows success message: "Reservation created successfully!"

---

## 2. Staff Views Pending Reservation

**Page:** Staff Dashboard → Reservations List

**Display:**
```
┌─────────────────────────────────────────────────────┐
│ Reservations List                                   │
├─────────────────────────────────────────────────────┤
│ Customer    | Date      | Time  | Guests | Status  │
│ John Doe    | Nov 10    | 7:00 PM |  4   | 🟡 Pending │
│ Jane Smith  | Nov 11    | 6:30 PM |  2   | 🟢 Confirmed │
│ Bob Wilson  | Nov 12    | 8:00 PM |  6   | 🔵 Approved │
└─────────────────────────────────────────────────────┘
```

**Action:** Staff clicks on pending reservation to view details

---

## 3. Staff Approves Reservation

**Page:** Staff → Reservation Details

**Layout:**
```
┌─────────────────────────────────────────┬───────────────────────┐
│ Reservation Details                     │ Actions               │
│                                         │                       │
│ Status: 🟡 Pending                      │ Update Status:        │
│ Customer: John Doe                      │ ┌──────────────────┐ │
│ Email: john@example.com                 │ │ [Dropdown]       │ │
│ Restaurant: The Fancy Place             │ │  Pending         │ │
│ Date: Nov 10, 2024                      │ │  Approved        │ │
│ Time: 7:00 PM                           │ │  Confirmed       │ │
│ Guests: 4                               │ │  Cancelled       │ │
│                                         │ │  Completed       │ │
│                                         │ └──────────────────┘ │
│                                         │ [Update Status]      │
│                                         │                       │
│                                         │ Quick Actions:        │
│                                         │ ┌──────────────────┐ │
│                                         │ │ ✓ Approve &      │ │
│                                         │ │   Notify Customer│ │
│                                         │ └──────────────────┘ │
└─────────────────────────────────────────┴───────────────────────┘
```

**Action:** Staff clicks "✓ Approve & Notify Customer"

**Result:**
- Status changes to `approved`
- Email sent to customer
- Success message: "Reservation approved. Customer will receive a confirmation email."
- UI updates to show:
  ```
  ⏳ Awaiting customer confirmation.
  An email was sent to john@example.com.
  ```

---

## 4. Customer Receives Email

**Email Subject:** "Your Reservation Has Been Approved - Confirm Now"

**Email Content:**
```
┌─────────────────────────────────────────────────────────┐
│              Reservation Approved!                       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ Hi John Doe,                                            │
│                                                          │
│ Great news! Your reservation request at The Fancy       │
│ Place has been approved by our staff.                   │
│                                                          │
│ ┌─────────────────────────────────────────────────────┐│
│ │ Date: Saturday, November 10, 2024                   ││
│ │ Time: 7:00 PM                                       ││
│ │ Guests: 4                                           ││
│ │ Table: Table 5                                      ││
│ └─────────────────────────────────────────────────────┘│
│                                                          │
│ Please confirm or cancel your reservation:              │
│                                                          │
│    ┌───────────────────┐    ┌──────────────────┐       │
│    │ ✓ Confirm         │    │ ✗ Cancel         │       │
│    │   Reservation     │    │                  │       │
│    └───────────────────┘    └──────────────────┘       │
│      (Green button)            (Red button)             │
│                                                          │
│ ⚠️ Please confirm to secure your table.                 │
│                                                          │
│ Thank you for choosing The Fancy Place!                 │
└─────────────────────────────────────────────────────────┘
```

---

## 5. Customer Confirms Reservation

**Action:** Customer clicks "✓ Confirm Reservation" in email

**Result:**
- Redirects to customer's reservations page
- Status changes to `confirmed`
- Table is now blocked/held
- Success message: "Reservation confirmed! See you soon."

**Customer's Reservations Page:**
```
┌─────────────────────────────────────────────────────────┐
│ My Reservations                                          │
├─────────────────────────────────────────────────────────┤
│ ✅ Reservation Confirmed!                                │
│                                                          │
│ The Fancy Place                                          │
│ 🗓️  Saturday, November 10, 2024 at 7:00 PM              │
│ 👥 4 guests                                              │
│ 🪑 Table 5                                               │
│ Status: 🟢 Confirmed                                     │
│                                                          │
│ [View Details]  [Cancel Reservation]                    │
└─────────────────────────────────────────────────────────┘
```

---

## 6. Staff View After Confirmation

**Page:** Staff → Reservation Details (refreshed)

**Status Update:**
```
┌─────────────────────────────────────────┬───────────────────────┐
│ Reservation Details                     │ Actions               │
│                                         │                       │
│ Status: 🟢 Confirmed                    │ Update Status:        │
│ Customer: John Doe                      │ [Dropdown: Confirmed] │
│ Email: john@example.com                 │ [Update Status]      │
│ Restaurant: The Fancy Place             │                       │
│ Date: Nov 10, 2024                      │ Quick Actions:        │
│ Time: 7:00 PM                           │ ┌──────────────────┐ │
│ Guests: 4                               │ │ Mark as          │ │
│ Table: Table 5                          │ │ Completed        │ │
│                                         │ └──────────────────┘ │
│                                         │                       │
│                                         │ ┌──────────────────┐ │
│                                         │ │ Cancel           │ │
│                                         │ │ Reservation      │ │
│                                         │ └──────────────────┘ │
└─────────────────────────────────────────┴───────────────────────┘
```

---

## 7. After Dining Event

**Action:** Staff clicks "Mark as Completed"

**Result:**
- Status changes to `completed`
- Table is freed (status set to 'available')
- Success message: "Reservation marked as completed."

---

## Alternative Flow: Customer Cancellation

**From Email:**
Customer clicks "✗ Cancel" button in approval email

**From Dashboard:**
Customer clicks "Cancel Reservation" on their reservations page

**Result:**
- Status changes to `cancelled`
- If table was assigned, it's freed
- Redirects to reservations list
- Message: "Reservation cancelled."

**Display:**
```
┌─────────────────────────────────────────────────────────┐
│ My Reservations                                          │
├─────────────────────────────────────────────────────────┤
│ The Fancy Place                                          │
│ 🗓️  Saturday, November 10, 2024 at 7:00 PM              │
│ 👥 4 guests                                              │
│ Status: 🔴 Cancelled                                     │
│                                                          │
│ [View Details]                                          │
└─────────────────────────────────────────────────────────┘
```

---

## Status Badge Colors

All status badges use this color scheme:

```
🟡 Pending    → Yellow background, yellow text (bg-yellow-100 text-yellow-800)
🔵 Approved   → Blue background, blue text (bg-blue-100 text-blue-800)
🟢 Confirmed  → Green background, green text (bg-green-100 text-green-800)
🔴 Cancelled  → Red background, red text (bg-red-100 text-red-800)
⚫ Completed  → Gray background, gray text (bg-gray-100 text-gray-800)
```

---

## Notification Messages

### Success Messages (Green)
- "Reservation created successfully!"
- "Reservation approved. Customer will receive a confirmation email."
- "Reservation status updated."
- "Reservation confirmed! See you soon."
- "Reservation marked as completed."
- "Reservation cancelled."

### Error Messages (Red)
- "Only pending reservations can be approved."
- "This reservation cannot be confirmed."
- "This reservation cannot be cancelled."
- "Only confirmed reservations can be completed."
- "Unauthorized access. Staff privileges required."
- "Unauthorized - This is not your reservation."

### Info Messages (Blue)
- "⏳ Awaiting customer confirmation. An email was sent to [email]."

---

## Responsive Design Notes

### Mobile View
- Stack reservation details vertically
- Full-width action buttons
- Collapsible sections for better space usage

### Tablet View
- 2-column layout maintained
- Slightly smaller padding

### Desktop View
- Full 3-column layout with sidebar
- Larger buttons and more whitespace

---

## Accessibility Features

- ✅ All buttons have descriptive text
- ✅ Forms have proper labels
- ✅ Status indicators use color + text (not just color)
- ✅ Keyboard navigation supported
- ✅ ARIA labels on interactive elements
- ✅ Focus states visible on all inputs
- ✅ Error messages clearly associated with fields

---

## Visual Hierarchy

**Priority Levels:**

1. **Critical Actions** (Green buttons)
   - Approve & Notify Customer
   - Confirm Reservation

2. **Primary Actions** (Blue buttons)
   - Update Status
   - Mark as Completed

3. **Destructive Actions** (Red buttons)
   - Cancel Reservation

4. **Secondary Actions** (Gray buttons)
   - View Details
   - Back to List
