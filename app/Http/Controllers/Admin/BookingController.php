<?php

namespace App\Http\Controllers\Admin;

use App\Booking;
use App\Enums\BookingRemark;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovedBookingRequest;
use App\Http\Requests\DeclinedBookingRequest;
use App\Http\Requests\SuggestBookingRequest;
use App\Notifications\ApprovedBooking;
use App\Notifications\DeclinedBooking;
use App\Notifications\SuggestBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.bookings.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'table']);
        // dd($booking->toArray());
        return view('admin.bookings.show', compact('booking'));
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
        $bookings = Booking::with(['table', 'user'])->get();

        return response()->json([
            'bookings' => $bookings,
        ], 200);
    }

    public function approved(Booking $booking, ApprovedBookingRequest $request)
    {
        $booking->remarks = $request->remarks;

        $booking->save();

        $this->notifyBooker($booking->load(['user']));

        return response()->json([
            'success' => true,
            'message' => "Booking Succesully Approved",
            'booking' => $booking->load(['user', 'table']),
        ], 201);
    }

    public function declined(Booking $booking, DeclinedBookingRequest $request)
    {
        $booking->remarks = $request->remarks;

        $booking->save();

        $this->notifyBooker($booking->load(['user']));

        return response()->json([
            'success' => true,
            'message' => "Booking Succesully Declined.",
            'booking' => $booking->load(['user', 'table']),
        ], 201);
    }

    public function suggest(Booking $booking, SuggestBookingRequest $request)
    {
        // dd($request->all());
        $suggestedSchedule = Carbon::parse($request->suggested_schedule)->setTimezone('Asia/Manila');

        $suggestedTime = Carbon::parse($request->suggested_time)->setTimezone('Asia/Manila');

        $suggestedSchedule->setTimeFrom($suggestedTime)->setTimezone('UTC');

        $bookings = Booking::whereDate('selected_schedule', $suggestedSchedule)
            ->where('table_id', $request->table_id)
            ->first();

        if ($bookings) {
            return response()->json([
                'success' => false,
                'message' => "Schedule already taken. Please suggest another schedule",
            ], 422);
        }

        if ($booking->remarks === BookingRemark::getValue('APPROVED')) {
            return response()->json([
                'success' => false,
                'message' => "Opps, sorry this booking is already APPROVED.",
            ], 422);
        }

        $booking->suggested_schedule = $suggestedSchedule;
        $booking->suggested_schedule_remark = BookingRemark::getValue('PENDING');

        $booking->save();

        $this->notifyBooker($booking->load(['user']), true);

        return response()->json([
            'success' => true,
            'booking' => $booking->load(['user', 'table']),
        ], 201);
    }

    private function notifyBooker($booking, $suggest = false)
    {
        // dd($booking->user);
        if ($suggest) {
            $booking->user->notify(new SuggestBooking($booking->load(['table']), $booking->user));
            return;
        }

        if ($booking->remarks->is(BookingRemark::APPROVED)) {
            $booking->user->notify(new ApprovedBooking($booking->load(['table']), $booking->user));
            return;
        }

        if ($booking->remarks->is(BookingRemark::DECLINED)) {
            $booking->user->notify(new DeclinedBooking($booking->load(['table']), $booking->user));
            return;
        }

    }
}
