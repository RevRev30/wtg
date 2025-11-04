<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Reservation;
use App\Mail\ReservationApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $staff;
    protected $restaurant;
    protected $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer@example.com',
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff@example.com',
        ]);

        // Create test restaurant
        $this->restaurant = Restaurant::factory()->create([
            'name' => 'Test Restaurant',
        ]);

        // Create pending reservation
        $this->reservation = Reservation::factory()->create([
            'customer_id' => $this->customer->id,
            'restaurant_id' => $this->restaurant->id,
            'status' => 'pending',
            'reservation_date' => now()->addDays(7),
            'reservation_time' => '19:00:00',
            'number_of_guests' => 4,
        ]);
    }

    /** @test */
    public function staff_can_approve_pending_reservation()
    {
        Mail::fake();

        $this->actingAs($this->staff)
            ->post(route('staff.reservations.approve', $this->reservation))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->reservation->refresh();
        $this->assertEquals('approved', $this->reservation->status);

        Mail::assertSent(ReservationApproved::class, function ($mail) {
            return $mail->hasTo($this->customer->email);
        });
    }

    /** @test */
    public function staff_cannot_approve_non_pending_reservation()
    {
        $this->reservation->update(['status' => 'confirmed']);

        $this->actingAs($this->staff)
            ->post(route('staff.reservations.approve', $this->reservation))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->reservation->refresh();
        $this->assertEquals('confirmed', $this->reservation->status);
    }

    /** @test */
    public function customer_can_confirm_approved_reservation()
    {
        $this->reservation->update(['status' => 'approved']);

        $this->actingAs($this->customer)
            ->get(route('reservations.customer-confirm', $this->reservation))
            ->assertRedirect(route('reservations.index'))
            ->assertSessionHas('success');

        $this->reservation->refresh();
        $this->assertEquals('confirmed', $this->reservation->status);
    }

    /** @test */
    public function customer_cannot_confirm_non_approved_reservation()
    {
        $this->reservation->update(['status' => 'pending']);

        $this->actingAs($this->customer)
            ->get(route('reservations.customer-confirm', $this->reservation))
            ->assertRedirect(route('reservations.index'))
            ->assertSessionHas('error');

        $this->reservation->refresh();
        $this->assertEquals('pending', $this->reservation->status);
    }

    /** @test */
    public function customer_can_cancel_their_reservation()
    {
        $this->reservation->update(['status' => 'approved']);

        $this->actingAs($this->customer)
            ->get(route('reservations.customer-cancel', $this->reservation))
            ->assertRedirect(route('reservations.index'))
            ->assertSessionHas('success');

        $this->reservation->refresh();
        $this->assertEquals('cancelled', $this->reservation->status);
    }

    /** @test */
    public function customer_cannot_access_other_customers_reservation()
    {
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($otherCustomer)
            ->get(route('reservations.customer-confirm', $this->reservation))
            ->assertForbidden();
    }

    /** @test */
    public function staff_can_manually_update_reservation_status()
    {
        $this->actingAs($this->staff)
            ->patch(route('staff.reservations.updateStatus', $this->reservation), [
                'status' => 'confirmed',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->reservation->refresh();
        $this->assertEquals('confirmed', $this->reservation->status);
    }

    /** @test */
    public function staff_can_complete_confirmed_reservation()
    {
        $this->reservation->update(['status' => 'confirmed']);

        $this->actingAs($this->staff)
            ->post(route('staff.reservations.complete', $this->reservation))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->reservation->refresh();
        $this->assertEquals('completed', $this->reservation->status);
    }

    /** @test */
    public function staff_cannot_complete_non_confirmed_reservation()
    {
        $this->reservation->update(['status' => 'pending']);

        $this->actingAs($this->staff)
            ->post(route('staff.reservations.complete', $this->reservation))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->reservation->refresh();
        $this->assertEquals('pending', $this->reservation->status);
    }

    /** @test */
    public function only_confirmed_reservations_block_tables()
    {
        // Pending reservation should not block
        $this->reservation->update(['status' => 'pending']);
        $this->assertFalse($this->reservation->isBlockingTable());

        // Approved reservation should not block
        $this->reservation->update(['status' => 'approved']);
        $this->assertFalse($this->reservation->isBlockingTable());

        // Confirmed reservation should block
        $this->reservation->update(['status' => 'confirmed']);
        $this->assertTrue($this->reservation->isBlockingTable());

        // Completed should not block
        $this->reservation->update(['status' => 'completed']);
        $this->assertFalse($this->reservation->isBlockingTable());

        // Cancelled should not block
        $this->reservation->update(['status' => 'cancelled']);
        $this->assertFalse($this->reservation->isBlockingTable());
    }

    /** @test */
    public function complete_approval_workflow()
    {
        Mail::fake();

        // 1. Initial state: pending
        $this->assertEquals('pending', $this->reservation->status);

        // 2. Staff approves
        $this->actingAs($this->staff)
            ->post(route('staff.reservations.approve', $this->reservation));
        
        $this->reservation->refresh();
        $this->assertEquals('approved', $this->reservation->status);
        
        Mail::assertSent(ReservationApproved::class);

        // 3. Customer confirms
        $this->actingAs($this->customer)
            ->get(route('reservations.customer-confirm', $this->reservation));
        
        $this->reservation->refresh();
        $this->assertEquals('confirmed', $this->reservation->status);
        $this->assertTrue($this->reservation->isBlockingTable());

        // 4. Staff completes
        $this->actingAs($this->staff)
            ->post(route('staff.reservations.complete', $this->reservation));
        
        $this->reservation->refresh();
        $this->assertEquals('completed', $this->reservation->status);
        $this->assertFalse($this->reservation->isBlockingTable());
    }

    /** @test */
    public function customer_middleware_prevents_unauthorized_access()
    {
        // Guest cannot access
        $this->get(route('reservations.customer-confirm', $this->reservation))
            ->assertRedirect(route('login'));

        // Staff cannot use customer routes
        $this->actingAs($this->staff)
            ->get(route('reservations.customer-confirm', $this->reservation))
            ->assertForbidden();
    }

    /** @test */
    public function staff_middleware_prevents_customer_access()
    {
        // Customer cannot access staff routes
        $this->actingAs($this->customer)
            ->post(route('staff.reservations.approve', $this->reservation))
            ->assertForbidden();
    }
}
