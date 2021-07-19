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
            'status' => $this->faker->randomElement([
                'pending',
                'failed',
                'completed'
            ]),
            'expires' => $this->faker->dateTimeBetween('+1 day', '+6 months')
        ];
    }
}
