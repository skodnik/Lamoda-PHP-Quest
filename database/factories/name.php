<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models;
use Faker\Generator as Faker;

$factory->define(App\Models\Name::class, function (Faker $faker) {

    return [
        'name' => $faker->firstNameFemale . $faker->colorName . $faker->locale,
    ];
});

