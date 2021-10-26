<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UploadUser;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => $this->faker->userName(),
            'mw_userid' => $this->faker->randomNumber(8, true)
        ];
    }


    /**
     * Indicate that the user is an uploader.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function uploader(string $username = null)
    {
        $username = $username ?? $this->faker->userName() . ' (uploader)';

        $uploader = UploadUser::factory()->create([
            'username' => $username
        ]);

        return $this->state(function (array $attributes) use ($uploader) {
            return [
                'username' => $uploader->getAttribute('username'),
            ];
        });
    }
}
