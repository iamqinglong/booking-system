<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Http\Requests\ApprovedBookingRequest;
use App\Http\Requests\BookingStoreRequest;
use App\Http\Requests\DeclinedBookingRequest;
use App\Notifications\UserBookingCreate;
// use DateTime;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Notification;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('user.bookings.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user.bookings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BookingStoreRequest $request)
    {

        $selectedTime = Carbon::parse($request->selected_time)->setTimezone('Asia/Manila');

        $selectedSchedule = Carbon::parse($request->selected_date)->setTimezone('Asia/Manila');

        $selectedSchedule->setTimeFrom($selectedTime)->setTimezone('UTC');

        $bookings = Booking::whereDate('selected_schedule', $selectedSchedule)
            ->where('table_id', $request->table_id)
            ->first();

        if ($bookings) {
            return response()->json([
                'success' => false,
                'message' => "Schedule already taken. Please pick another schedule or change table.",
            ], 422);
        }

        $formData = array_merge(
            $request->all(), [
                'user_id' => auth()->user()->id,
                'selected_schedule' => $selectedSchedule,
            ]);

        $booking = Booking::create($formData)->refresh();

        $booking->selected_schedule = Carbon::parse($booking->selected_schedule)->toISOString();

        $booking->selected_time = $request->selected_time;

        // dd($booking->load(['table'])->toArray());
        $this->sendAdminNotification(auth()->user(), $booking->load(['table']));

        return response()->json([
            'bookings' => $booking,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {
        //
    }

    public function all()
    {
        $id = auth()->user()->id;

        $bookings = Booking::with(['table'])->where('user_id', $id)->get();
        // $bookings->map(function ($val) {
        //     dd($val->remarks);
        // });
        return response()->json([
            'bookings' => $bookings,
        ], 200);
    }

    private function sendAdminNotification($booker, $booking)
    {
        $user = User::where('email', 'admin@booking.com')->first();

        Notification::send($user, new UserBookingCreate($booking, $booker));
    }

    public function approved(Booking $booking, ApprovedBookingRequest $request)
    {
        $booking->suggested_schedule_remark = $request->remarks;

        $booking->save();

        return response()->json([
            'success' => true,
            'message' => "Booking Succesully Approved",
            'booking' => $booking->load(['user', 'table']),
        ], 201);
    }

    public function declined(Booking $booking, DeclinedBookingRequest $request)
    {
        $booking->suggested_schedule_remark = $request->remarks;

        $booking->save();

        return response()->json([
            'success' => true,
            'message' => "Booking Succesully Declined.",
            'booking' => $booking->load(['user', 'table']),
        ], 201);
    }

}
