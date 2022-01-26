<?php

namespace Database\Factories;

use App\Models\ImportMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for the ImportMeta model
 *
 * This factory generates a random ImportMeta resource with a description,
 * a status, an external source (with a 60% chance including a URL), an
 * expiry data and the common file name 'test_file.csv'.
 */
class ImportMetaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ImportMeta::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'description' => $this->faker->optional(0.8)->realText(350),
            // Exclude failed, so that we could associate it with an Import Failure
            'status' => $this->faker->randomElement([
                'pending',
                'completed'
            ]),
            'external_source' => $this->faker->realText(100),
            'external_source_url' => $this->faker->optional(0.6)->url(),
            'expires' => $this->faker->dateTimeBetween('+1 day', '+6 months')->format('Y-m-d'),
            'filename' => 'test_file.csv'
        ];
    }

    /**
     * Indicate that the import is failed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function failed()
    {
        return $this->state(function () {
            return [
                'status' => 'failed',
            ];
        });
    }

    /**
     * Indicate that the import is expired.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function expired()
    {
        return $this->state(function () {
            return [
                'expires' => $this->faker->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d'),
            ];
        });
    }
}
