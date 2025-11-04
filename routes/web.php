<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\StaffReservationController;
use App\Http\Controllers\StaffRestaurantController;
use App\Http\Controllers\StaffCustomerController;
use App\Http\Controllers\SeatingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Landing page
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'staff' || $user->role === 'admin') {
            return redirect()->route('staff.dashboard');
        }
    }
    return view('landing');
})->name('landing');

// Dashboard route - role-aware
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user && ($user->role === 'staff' || $user->role === 'admin')) {
        return redirect()->route('staff.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Customer routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create/{restaurant}', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    
    // Customer confirmation routes
    Route::get('/reservations/{reservation}/customer-confirm', [ReservationController::class, 'customerConfirm'])->name('reservations.customer-confirm');
    Route::get('/reservations/{reservation}/customer-cancel', [ReservationController::class, 'customerCancel'])->name('reservations.customer-cancel');
});

// Staff routes
Route::middleware(['auth', 'staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    
    // Reservations
    Route::get('/reservations', [StaffReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [StaffReservationController::class, 'show'])->name('reservations.show');
    Route::patch('/reservations/{reservation}/status', [StaffReservationController::class, 'updateStatus'])->name('reservations.updateStatus');
    Route::post('/reservations/{reservation}/approve', [StaffReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation}/complete', [StaffReservationController::class, 'complete'])->name('reservations.complete');
    Route::post('/reservations/{reservation}/cancel', [StaffReservationController::class, 'cancel'])->name('reservations.cancel');
    
    // Restaurants
    Route::get('/restaurants', [StaffRestaurantController::class, 'index'])->name('restaurants.index');
    
    // Customers
    Route::get('/customers', [StaffCustomerController::class, 'index'])->name('customers.index');
    
    // Seating
    Route::get('/seating', [SeatingController::class, 'index'])->name('seating.index');
    Route::get('/seating/advanced', [SeatingController::class, 'advanced'])->name('seating.advanced');
    Route::get('/seating/data', [SeatingController::class, 'getSeatingData'])->name('seating.data');
    Route::post('/seating/assign', [SeatingController::class, 'assignTable'])->name('seating.assign');
    Route::post('/seating/reassign', [SeatingController::class, 'reassignTable'])->name('seating.reassign');
    Route::post('/seating/update-status', [SeatingController::class, 'updateTableStatus'])->name('seating.updateStatus');
});

require __DIR__.'/auth.php';
