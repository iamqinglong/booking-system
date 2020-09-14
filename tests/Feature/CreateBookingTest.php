<?php

namespace Tests\Feature;

// use Illuminate\Http\Response;
use App\Booking;
use App\Notifications\UserBookingCreate;
use App\Table;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateBookingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('passport:install');
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        Artisan::call('db:seed', ['--class' => 'BookingSeeder']);
        $this->signIn();
    }

    /** @test */
    public function a_user_can_go_to_create_booking_form()
    {

        $this->get('/user/bookings/create')
            ->assertSeeText('Book a Table');
    }

    /** @test */
    public function a_user_can_create_a_booking()
    {
        $this->withoutExceptionHandling();

        // $table = Table::inRandomOrder()->first();
        $table = create(Table::class);
        $booking = raw(Booking::class);

        $selected_schedule = Carbon::parse($booking['selected_schedule'])->setTimezone('UTC');

        $selected_time = Carbon::parse($booking['selected_schedule'])->setTimezone('UTC');

        $selected_time->addHours(rand(1, 12));

        $request = [
            'selected_date' => $selected_schedule->toISOString(),
            'selected_time' => $selected_time->toISOString(),
            'number_of_persons' => $booking['number_of_persons'],
            'table_id' => $table->id,
        ];

        $this->post(route('user.bookings.store'), $request)
            ->assertSessionHasNoErrors()
            ->assertJsonStructure(['bookings']);

        $selected_time = Carbon::parse($selected_time)->setTimezone('Asia/Manila');

        $selected_schedule = Carbon::parse($selected_schedule)->setTimezone('Asia/Manila');

        $selected_schedule->setTimeFrom($selected_time)->setTimezone('UTC');

        $this->assertDatabaseHas('bookings', [
            'selected_schedule' => $selected_schedule->format('Y-m-d H:i:s'),
            'number_of_persons' => $booking['number_of_persons'],
            'user_id' => auth()->user()->id,
            'table_id' => $table->id,
        ]);

        Notification::fake();

        $user = User::where('email', 'admin@booking.com')->first();
        $booking = Booking::where('selected_schedule', $selected_schedule->format('Y-m-d H:i:s'))
            ->where('number_of_persons', $booking['number_of_persons'])
            ->where('user_id', auth()->user()->id)
            ->where('table_id', $table->id)
            ->first();
        $notification = new UserBookingCreate($booking->load(['table']), auth()->user());

        $user->notify($notification);

        $this->assertEquals(['mail'], $notification->via($user));
        // Notification::assertSentTo(
        //     $user,
        //     UserBookingCreate::class
        // );
    }

    /** @test */
    public function all_fields_are_required()
    {
        $this->withoutExceptionHandling();

        $this->expectException(ValidationException::class);

        $response = $this->postJson('/user/bookings')->assertStatus(422);
    }
}
