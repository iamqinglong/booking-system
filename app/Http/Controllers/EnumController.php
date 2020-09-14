<?php

namespace App\Http\Controllers;

use App\Enums\BookingRemark;
use App\Enums\SuggestedScheduleRemark;

class EnumController extends Controller
{
    public function getBookingRemarks()
    {
        return response()->json([
            'booking_remarks' => BookingRemark::asArray(),
        ], 200);
    }

    public function getSuggestedScheduleRemarks(Type $var = null)
    {
        return response()->json([
            'suggested_schedule_remarks' => SuggestedScheduleRemark::asArray(),
        ], 200);
    }
}
