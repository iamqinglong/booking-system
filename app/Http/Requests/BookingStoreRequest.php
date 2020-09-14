<?php

namespace App\Http\Requests;

use App;
use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
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
        // if (app()->runningUnitTests()) {
        //     return [
        //         'selected_date' => 'required',
        //         'selected_time' => 'required',
        //         'number_of_persons' => 'required|numeric',
        //         'table_id' => 'required|exists:tables,id',
        //     ];
        // }
        return [
            'selected_date' => 'required|date',
            'selected_time' => 'required|date',
            'number_of_persons' => 'required|numeric',
            'table_id' => 'required|exists:tables,id',
        ];
    }

    public function messages()
    {
        return [
            'selected_date.required' => 'Please pick a date.',
            'selected_time.required' => 'Please pick your dining time.',
            'number_of_persons.required' => "Please input how many diner/s.",
            'table_id.required' => 'Please pick your desired Table.',
        ];
    }
}
