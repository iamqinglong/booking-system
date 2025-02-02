<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Table;
use Faker\Generator as Faker;

$factory->define(Table::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'number_of_seats' => $faker->randomDigitNotNull(),
    ];
});
