<?php

namespace Database\Factories;

use App\Models\ImportFailure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for the ImportFailure model
 *
 * This factory generates a random ImportFailure with an 80% chance to include
 * a line number betwen 0 and 10,000 and an arbitrary error message of up to
 * 15 random words.
 */
class ImportFailureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ImportFailure::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $randomLine = $this->faker->optional(0.8)->numberBetween(0, 10000);
        $randomWordAmount = $this->faker->numberBetween(1, 15);
        $randomSentence = $this->faker->sentence($randomWordAmount);
        $message = $randomLine ? $this->faker->randomElement([
            __('validation.import.error', [
                'line' => $randomLine,
                'message' => $randomSentence
            ]),
            __('parsing.import.error', [
                'line' => $randomLine,
                'message' => $randomSentence
            ])
        ]) : $randomSentence;

        return [
            // Some errors might not include a line number (80% chance that they do).
            // Assuming a file might contain up to 10,000 lines.
            'line' => $randomLine,
            'message' => $message
        ];
    }
}
