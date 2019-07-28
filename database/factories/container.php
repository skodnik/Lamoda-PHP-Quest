<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models;
use Faker\Generator as Faker;

$factory->define(App\Models\Container::class, function (Faker $faker) {
    return [
        'id' => $faker->numberBetween($min = 1, $max = 99999999),
        'name' => $faker->firstNameFemale,
    ];
});
