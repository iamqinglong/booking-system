<?php

namespace App;

use App\Enums\BookingRemark;
use App\Enums\SuggestedScheduleRemark;
use BenSampo\Enum\Traits\CastsEnums;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use CastsEnums;

    protected $casts = [
        'remarks' => BookingRemark::class,
        'suggested_schedule_remark' => SuggestedScheduleRemark::class,
    ];

    // protected $dates = ['suggested_schedule', 'selected_schedule'];

    protected $fillable = [
        'selected_schedule',
        'suggested_schedule',
        'number_of_persons',
        'remarks',
        'suggested_schedule_remark',
        'table_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function getSelectedScheduleAttribute($value)
    {
        return Carbon::parse($value)->toISOString();
    }

}
