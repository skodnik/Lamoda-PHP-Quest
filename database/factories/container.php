<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models;
use Faker\Generator as Faker;

$factory->define(App\Models\Container::class, function (Faker $faker) {

    if ($_ENV['INCREMENT_IDS']) {
        return [
            'name' => $faker->firstNameFemale,
        ];
    } else {
        return [
            'id' => $faker->numberBetween($min = 1, $max = 99999999),
            'name' => $faker->firstNameFemale,
        ];
    }
});
