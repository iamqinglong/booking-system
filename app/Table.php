<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeAvailable()
    {
        return $this->where('availability', true);
    }
}
