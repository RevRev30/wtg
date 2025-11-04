<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::where('customer_id', auth()->id())
            ->with(['restaurant', 'table'])
            ->latest()
            ->get();
        
        return view('reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation)
    {
        // Ensure customer owns this reservation
        if ($reservation->customer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return view('reservations.show', compact('reservation'));
    }

    public function create(Restaurant $restaurant)
    {
        return view('reservations.create', compact('restaurant'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
        ]);

        $reservation = Reservation::create([
            'customer_id' => auth()->id(),
            'restaurant_id' => $validated['restaurant_id'],
            'reservation_date' => $validated['reservation_date'],
            'reservation_time' => $validated['reservation_time'],
            'number_of_guests' => $validated['number_of_guests'],
            'special_requests' => $validated['special_requests'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('reservations.index')->with('success', 'Reservation created successfully!');
    }

    public function customerConfirm(Reservation $reservation)
    {
        // Ensure customer owns this reservation
        if ($reservation->customer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($reservation->status !== 'approved') {
            return redirect()->route('reservations.index')->with('error', 'This reservation cannot be confirmed.');
        }

        $reservation->update(['status' => 'confirmed']);

        return redirect()->route('reservations.index')->with('success', 'Reservation confirmed! See you soon.');
    }

    public function customerCancel(Reservation $reservation)
    {
        // Ensure customer owns this reservation
        if ($reservation->customer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (in_array($reservation->status, ['completed', 'cancelled'])) {
            return redirect()->route('reservations.index')->with('error', 'This reservation cannot be cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('reservations.index')->with('success', 'Reservation cancelled.');
    }
}
