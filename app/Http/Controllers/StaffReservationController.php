<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class StaffReservationController extends Controller
{
    public function index()
    {
        // List reservations logic
        $reservations = Reservation::with(['customer', 'restaurant', 'table'])
            ->latest()
            ->paginate(20);
        
        return view('staff.reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['customer', 'restaurant', 'table']);
        return view('staff.reservations.show', compact('reservation'));
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,confirmed,cancelled,completed'
        ]);

        $reservation->update(['status' => $request->status]);

        // If manually set to approved, send email
        if ($request->status === 'approved' && $reservation->wasChanged('status')) {
            Mail::to($reservation->customer->email)->send(new \App\Mail\ReservationApproved($reservation));
        }

        return back()->with('success', 'Reservation status updated.');
    }

    public function approve(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Only pending reservations can be approved.');
        }

        $reservation->update(['status' => 'approved']);

        // Send email to customer with confirmation link
        Mail::to($reservation->customer->email)->send(new \App\Mail\ReservationApproved($reservation));

        return back()->with('success', 'Reservation approved. Customer will receive a confirmation email.');
    }

    public function complete(Reservation $reservation)
    {
        if ($reservation->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed reservations can be completed.');
        }

        $reservation->update(['status' => 'completed']);

        // Free the table if assigned
        if ($reservation->table) {
            $reservation->table->update(['status' => 'available']);
        }

        return back()->with('success', 'Reservation marked as completed.');
    }

    public function cancel(Reservation $reservation)
    {
        if (in_array($reservation->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This reservation cannot be cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        // Free the table if assigned
        if ($reservation->table) {
            $reservation->table->update(['status' => 'available']);
        }

        return back()->with('success', 'Reservation cancelled.');
    }
}
