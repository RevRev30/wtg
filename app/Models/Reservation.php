<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'table_id',
        'reservation_date',
        'reservation_time',
        'number_of_guests',
        'special_requests',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'reservation_time' => 'datetime',
    ];

    /**
     * Get the customer that owns the reservation.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the restaurant for this reservation.
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Get the table assigned to this reservation.
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Scope a query to only include reservations with specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include reservations that block tables.
     * Only confirmed reservations block tables.
     */
    public function scopeBlockingTables($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Check if this reservation blocks a table.
     */
    public function isBlockingTable(): bool
    {
        return $this->status === 'confirmed';
    }
}
