<?php

namespace Database\Factories;

use App\Models\ImportMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

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
    public function failed(string $username = null)
    {
        return $this->state(function (array $attributes) {
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
    public function expired(string $username = null)
    {
        return $this->state(function (array $attributes) {
            return [
                'expires' => $this->faker->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d'),
            ];
        });
    }
}
