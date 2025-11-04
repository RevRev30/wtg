@extends('layouts.staff')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('staff.reservations.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Back to Reservations
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Reservation Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">Reservation Details</h1>
                
                <div class="space-y-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Status</span>
                        <p class="mt-1">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                @if($reservation->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($reservation->status === 'approved') bg-blue-100 text-blue-800
                                @elseif($reservation->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($reservation->status === 'cancelled') bg-red-100 text-red-800
                                @elseif($reservation->status === 'completed') bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($reservation->status) }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <span class="text-sm font-medium text-gray-500">Customer</span>
                        <p class="mt-1 text-gray-900">{{ $reservation->customer->name }}</p>
                        <p class="text-sm text-gray-600">{{ $reservation->customer->email }}</p>
                    </div>

                    <div>
                        <span class="text-sm font-medium text-gray-500">Restaurant</span>
                        <p class="mt-1 text-gray-900">{{ $reservation->restaurant->name }}</p>
                    </div>

                    <div>
                        <span class="text-sm font-medium text-gray-500">Date & Time</span>
                        <p class="mt-1 text-gray-900">
                            {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('l, F d, Y') }}
                            at {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('g:i A') }}
                        </p>
                    </div>

                    <div>
                        <span class="text-sm font-medium text-gray-500">Number of Guests</span>
                        <p class="mt-1 text-gray-900">{{ $reservation->number_of_guests }}</p>
                    </div>

                    @if($reservation->table)
                    <div>
                        <span class="text-sm font-medium text-gray-500">Table</span>
                        <p class="mt-1 text-gray-900">{{ $reservation->table->name }}</p>
                    </div>
                    @endif

                    @if($reservation->special_requests)
                    <div>
                        <span class="text-sm font-medium text-gray-500">Special Requests</span>
                        <p class="mt-1 text-gray-900">{{ $reservation->special_requests }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Status Update -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h2>
                <form action="{{ route('staff.reservations.updateStatus', $reservation) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" id="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $reservation->status === 'approved' ? 'selected' : '' }}>Approved (Awaiting Customer)</option>
                                <option value="confirmed" {{ $reservation->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ $reservation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    @if($reservation->status === 'pending')
                        <form action="{{ route('staff.reservations.approve', $reservation) }}" method="POST" class="inline-block w-full">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                ✓ Approve & Notify Customer
                            </button>
                        </form>
                    @endif

                    @if($reservation->status === 'approved')
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <p class="text-sm text-blue-800">
                                <strong>⏳ Awaiting customer confirmation.</strong><br>
                                An email was sent to {{ $reservation->customer->email }}.
                            </p>
                        </div>
                    @endif

                    @if($reservation->status === 'confirmed')
                        <form action="{{ route('staff.reservations.complete', $reservation) }}" method="POST" class="inline-block w-full">
                            @csrf
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Mark as Completed
                            </button>
                        </form>
                    @endif

                    @if($reservation->status !== 'cancelled' && $reservation->status !== 'completed')
                        <form action="{{ route('staff.reservations.cancel', $reservation) }}" method="POST" class="inline-block w-full">
                            @csrf
                            <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                Cancel Reservation
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
