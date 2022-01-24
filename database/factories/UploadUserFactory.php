<?php

namespace Database\Factories;

use App\Models\UploadUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for the UploadUser model
 *
 * This factory generates an UploadUser resource with a random user name.
 */
class UploadUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UploadUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => $this->faker->userName()
        ];
    }
}
