<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuggestBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // dd($this->all());
        return [
            'suggested_schedule' => 'required|date',
            'suggested_time' => 'required|date',
            'booking_id' => 'required|exists:bookings,id',
            'table_id' => 'required|exists:tables,id',
        ];
    }
}
