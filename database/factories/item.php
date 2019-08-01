<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models;
use App\Models\Container;
use App\Models\Name;
use App\Models\Item;
use Faker\Generator as Faker;

$factory->define(Item::class, function (Faker $faker) {

    if ($_ENV['INCREMENT_IDS']) {
        return [
            'name_id' => random_int(1, $_ENV['QUANTITY_UNIQUE_NAMES']),
        ];
    } else {
        return [
            'id' => $faker->numberBetween(1, 99999999),
            'name_id' => random_int(1, $_ENV['QUANTITY_UNIQUE_NAMES']),
        ];
    }

});
