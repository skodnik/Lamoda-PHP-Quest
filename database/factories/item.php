<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models;
use App\Models\Container;
use App\Models\Name;
use App\Models\Item;
use Faker\Generator as Faker;

$factory->define(Item::class, function (Faker $faker) {
    return [
        'id' => $faker->numberBetween($min = 1, $max = 99999999),
        'container_id' => Container::all()->random()->id,
        'name_id' => Name::all()->random()->id,
    ];
});
