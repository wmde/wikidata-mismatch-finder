<?php

namespace Database\Factories;

use App\Models\Mismatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class MismatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mismatch::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'statement_guid' => 'Q' . $this->faker->randomNumber() . '$' . $this->faker->uuid(),
            'property_id' => 'P' . $this->faker->randomNumber(),
            'wikidata_value' => $this->getRandomValue(),
            'external_value'=> $this->getRandomValue(),
            'external_url' => $this->faker->optional(0.6)->url()
        ];
    }

    /**
     * Indicate that a mismatch has been reviewed
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function reviewed()
    {
        return $this->state(function (array $attributes) {
            return [
                'review_status' => $this->getRandomReviewStatus()
            ];
        });
    }

    private function getRandomValue()
    {
        $randomWordAmount = $this->faker->numberBetween(1, 5);
        $randomDecimalLength = $this->faker->numberBetween(1, 4);

        // Return one random value of any of the random value types below,
        // to mimic data that might be in wikidata or external databases
        return $this->faker->randomElement([
            // A random date
            $this->faker->date(),
            // A random floating point number with up to 5 + 4 digits
            $this->faker->randomFloat($randomDecimalLength, 0, 99999),
            // A random integer with up to 9 digits
            $this->faker->randomNumber(9),
            // A random lorem text with up to 5 words
            $this->faker->words($randomWordAmount, true)
        ]);
    }

    private function getRandomReviewStatus()
    {
        return $this->faker->randomElement([
            'wikidata',
            'external',
            'both',
            'none'
        ]);
    }
}
