<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8fafc; }
        .button { display: inline-block; padding: 12px 24px; margin: 10px 5px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-confirm { background: #16a34a; color: white; }
        .btn-cancel { background: #dc2626; color: white; }
        .details { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reservation Approved!</h1>
        </div>
        <div class="content">
            <p>Hi {{ $reservation->customer->name }},</p>
            
            <p>Great news! Your reservation request at <strong>{{ $reservation->restaurant->name }}</strong> has been approved by our staff.</p>
            
            <div class="details">
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('l, F d, Y') }}</p>
                <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('g:i A') }}</p>
                <p><strong>Guests:</strong> {{ $reservation->number_of_guests }}</p>
                @if($reservation->table)
                <p><strong>Table:</strong> {{ $reservation->table->name }}</p>
                @endif
            </div>

            <p><strong>Please confirm or cancel your reservation:</strong></p>
            
            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ route('reservations.customer-confirm', $reservation) }}" class="button btn-confirm">✓ Confirm Reservation</a>
                <a href="{{ route('reservations.customer-cancel', $reservation) }}" class="button btn-cancel">✗ Cancel</a>
            </div>

            <p style="color: #dc2626; font-size: 14px;">⚠️ Please confirm to secure your table.</p>

            <p>Thank you for choosing {{ $reservation->restaurant->name }}!</p>
        </div>
    </div>
</body>
</html>
