<?php

namespace Database\Factories;

use App\Models\ImportMeta;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\UploadUser;

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
}
