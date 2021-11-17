<?php

use Faker\Generator as Faker;


$factory->define(App\Chime::class, function (Faker $faker) {
    $temp = new \App\Chime;
    return [
        'access_code' => $temp->getUniqueCode(),
        'name' => $faker->words(3, true),
    ];
});
