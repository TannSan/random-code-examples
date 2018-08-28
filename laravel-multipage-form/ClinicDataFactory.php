<?php

use Faker\Generator as Faker;

/**
 * The seed data for the clinic has to be constructed in a very particular order to ensure that all the values are correct in sequence.
 * Otherwise the data is wrong and the seeded charts look terrible.
 */
$factory->define(App\ClinicData::class, function (Faker $faker) {

    $cycle_1_started = $faker->numberBetween(500, 1000); // Q2
    $pregnant_total = $faker->numberBetween(300, $cycle_1_started); // Q3

    $cycle_4_pregnant = $faker->numberBetween(0, floor(0.1 * $pregnant_total)); // Q3d
    $cycle_3_pregnant = $faker->numberBetween($cycle_4_pregnant, floor(0.25 * $pregnant_total)); // Q3c
    $cycle_2_pregnant = $faker->numberBetween($cycle_3_pregnant, floor(0.3 * $pregnant_total)); // Q3b
    $cycle_1_pregnant = $pregnant_total - $cycle_4_pregnant - $cycle_3_pregnant - $cycle_2_pregnant; // Q3a

    $cycle_1_not_pregnant = $cycle_1_started - $cycle_1_pregnant; // Q4a

    $cycle_2_started = $faker->numberBetween($cycle_4_pregnant + $cycle_3_pregnant + $cycle_2_pregnant, $cycle_1_not_pregnant); // Q4b
    $cycle_2_not_pregnant = $cycle_2_started - $cycle_2_pregnant; // Q5a

    $cycle_3_started = $faker->numberBetween($cycle_4_pregnant + $cycle_3_pregnant, $cycle_2_not_pregnant); // Q5b
    $cycle_3_not_pregnant = $cycle_3_started - $cycle_3_pregnant; // Q6a

    $cycle_4_started = $faker->numberBetween($cycle_4_pregnant, $cycle_3_not_pregnant); // Q6b

    if ($cycle_1_not_pregnant == 0) {
        $cycle_1_dropouts = 0;
    } else {
        $cycle_1_dropouts = $cycle_2_started == 0 ? 100 : round(($cycle_1_not_pregnant - $cycle_2_started) / $cycle_1_not_pregnant * 100);
    }

    if ($cycle_2_not_pregnant == 0) {
        $cycle_2_dropouts = 0;
    } else {
        $cycle_2_dropouts = $cycle_3_started == 0 ? 100 : round(($cycle_2_not_pregnant - $cycle_3_started) / $cycle_2_not_pregnant * 100);
    }

    if ($cycle_3_not_pregnant == 0) {
        $cycle_3_dropouts = 0;
    } else {
        $cycle_3_dropouts = $cycle_4_started == 0 ? 100 : round(($cycle_3_not_pregnant - $cycle_4_started) / $cycle_3_not_pregnant * 100);
    }

    return [
        'user_id' => $faker->numberBetween(1, 5),
        'last_step_viewed' => $faker->numberBetween(1, 6),
        'completed' => $faker->numberBetween(0, 1),

        // Clinic Details
        'name' => $faker->company,
        'country' => $faker->countryCode,
        'year' => $faker->numberBetween(date("Y") - 5, date("Y")),
        'total_ivf_cycles' => $faker->numberBetween(500, 1000),
        'total_insemenation_cycles' => $faker->numberBetween(500, 1000),
        'is_individual_clinic' => $faker->numberBetween(0, 1),
        'is_public_clinic' => $faker->numberBetween(0, 1),

        // Primary Questions
        // Page 1 Q1-3
        'total_inquiries' => $faker->numberBetween(400, 1000),
        'total_women_started_treatments' => $cycle_1_started,
        'total_pregnancies' => $pregnant_total,
        // Page 2 Q3a-3d
        'total_pregnant_from_first_cycle' => $cycle_1_pregnant,
        'total_pregnant_from_second_cycle' => $cycle_2_pregnant,
        'total_pregnant_from_third_cycle' => $cycle_3_pregnant,
        'total_pregnant_from_further_cycles' => $cycle_4_pregnant,
        // Page 3 Q4a-4b Q5a-5b Q6a-6b
        'total_not_pregnant_from_first_cycle' => $cycle_1_not_pregnant,
        'total_not_pregnant_started_second_cycle' => $cycle_2_started,
        'total_not_pregnant_from_second_cycle' => $cycle_2_not_pregnant,
        'total_not_pregnant_started_third_cycle' => $cycle_3_started,
        'total_not_pregnant_from_third_cycle' => $cycle_3_not_pregnant,
        'total_not_pregnant_started_further_cycles' => $cycle_4_started,
        // Used in the benchmark chart to speed up the AJAX requests
        'percent_dropouts_from_first_cycle' => $cycle_1_dropouts,
        'percent_dropouts_from_second_cycle' => $cycle_2_dropouts,
        'percent_dropouts_from_third_cycle' => $cycle_3_dropouts,

        // Further Questions
        // Page 1
        'referral_1' => $faker->numberBetween(1, 7),
        'referral_2' => $faker->numberBetween(1, 7),
        'referral_3' => $faker->numberBetween(1, 7),
        'referral_other_1' => $faker->company,
        'referral_other_2' => $faker->company,
        //'referral_other_3',
        'dropout_factor_1' => $faker->numberBetween(1, 4),
        'dropout_factor_2' => $faker->numberBetween(1, 4),
        'dropout_factor_3' => $faker->numberBetween(1, 4),
        'dropout_factor_other_1' => $faker->company,
        //'dropout_factor_other_2',
        'dropout_factor_other_3' => $faker->company,
        // Page2
        'previous_treatment_had_one_cycle' => $faker->numberBetween(20, 300),
        'previous_treatment_had_two_cycles' => $faker->numberBetween(5, 300),
        'previous_treatment_had_three_cycles' => $faker->numberBetween(20, 300),
        'previous_treatment_had_further_cycles' => $faker->numberBetween(5, 300),

        'updated_at' => $faker->dateTimeBetween('-20 years'),
    ];
});
