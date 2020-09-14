<?php

namespace Tests\Feature;

use App\Booking;
use App\Enums\BookingRemark;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Tests\TestCase;

class ChangeBookingStatusTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('passport:install');
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        Artisan::call('db:seed', ['--class' => 'BookingSeeder']);

    }

    /** @test */
    public function approved_booking()
    {
        $this->signInAdmin();

        $this->withoutExceptionHandling();

        $booking = Booking::inRandomOrder()->first();

        $remarks = BookingRemark::getValue('APPROVED');

        $this->postJson("/admin/bookings/approved/$booking->id", ['remarks' => $remarks])
            ->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('bookings', [
            'remarks' => $remarks,
            'id' => $booking->id,
        ]);
    }

    /** @test */
    public function declined_booking()
    {
        $this->signInAdmin();

        $this->withoutExceptionHandling();

        $booking = Booking::inRandomOrder()->first();

        $remarks = BookingRemark::getValue('DECLINED');

        $this->postJson("/admin/bookings/approved/$booking->id", ['remarks' => $remarks])
            ->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('bookings', [
            'remarks' => $remarks,
            'id' => $booking->id,
        ]);
    }

    /** @test */
    public function non_admin_redirect()
    {
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedException::class);
        $this->signIn();
        $this->get("/admin/bookings")->assertForbidden();
    }
}
