<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Booking;
use Faker\Generator as Faker;

$factory->define(Booking::class, function (Faker $faker) {
    return [
        'number_of_persons' => $faker->randomDigitNotNull(),
        'selected_schedule' => $faker->dateTimeBetween('this week', '+1 days'),
    ];
});
