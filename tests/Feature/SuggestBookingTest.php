<?php

namespace Tests\Feature;

use App\Booking;
use App\Table;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SuggestBookingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('passport:install');
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        Artisan::call('db:seed', ['--class' => 'BookingSeeder']);
        $this->signInAdmin();
    }

    /** @test */
    public function suggest_booking_schedule()
    {
        $this->withoutExceptionHandling();

        $user = create(User::class);
        $table = create(Table::class);
        $booking = create(Booking::class, [
            'user_id' => $user->id,
            'table_id' => $table->id,
            'selected_schedule' => Carbon::now()->setTimezone('UTC')->toISOString(),
        ]);

        $suggestedBooking = raw(Booking::class);

        // $availableSchedule = false;

        // while (!$availableSchedule) {
        //     $suggestedBooking = raw(Booking::class);

        //     $suggestedSelectedSchedule = Carbon::parse($suggestedBooking['selected_schedule'])->setTimezone('UTC');

        //     $suggestedSelectedTime = Carbon::parse($suggestedBooking['selected_schedule'])->setTimezone('UTC');

        //     $suggestedSelectedTime->addHours(rand(1, 12));

        //     $suggestedSelectedTime = Carbon::parse($suggestedSelectedTime)->setTimezone('Asia/Manila');

        //     $suggestedSelectedSchedule = Carbon::parse($suggestedSelectedSchedule)->setTimezone('Asia/Manila');

        //     $suggestedSelectedSchedule->setTimeFrom($suggestedSelectedTime)->setTimezone('UTC');

        //     $bookingSelectedSchedule = Carbon::parse($booking->selected_schedule)->setTimezone('UTC');

        //     if ($bookingSelectedSchedule->notEqualTo($suggestedSelectedSchedule)) {
        //         $availableSchedule = true;
        //     }
        // }

        $suggestedSelectedSchedule = Carbon::now()->addDays(2)->setTimezone('UTC');

        $suggestedSelectedTime = Carbon::now()->addHours(rand(1, 12))->setTimezone('UTC');
        // dd($booking->toArray(), $suggestedSelectedSchedule->toISOString());
        $this->postJson("/admin/bookings/suggestion/$booking->id", [
            'suggested_schedule' => $suggestedSelectedSchedule->toISOString(),
            'suggested_time' => $suggestedSelectedTime->toISOString(),
            'table_id' => $table->id,
            'booking_id' => $booking->id,
        ])
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'booking']);

        $suggestedSelectedTime = Carbon::parse($suggestedSelectedTime)->setTimezone('Asia/Manila');

        $suggestedSelectedSchedule = Carbon::parse($suggestedSelectedSchedule)->setTimezone('Asia/Manila');

        $suggestedSelectedSchedule->setTimeFrom($suggestedSelectedTime)->setTimezone('UTC');

        $this->assertDatabaseHas('bookings', [
            'suggested_schedule' => $suggestedSelectedSchedule->format('Y-m-d H:i:s'),
            'user_id' => $user->id,
            'table_id' => $table->id,
        ]);
    }
}
