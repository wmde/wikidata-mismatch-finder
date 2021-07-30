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

    private function getRandomValue()
    {
        $randomWordAmount = $this->faker->numberBetween(1, 5);
        $randomDecimalLength = $this->faker->numberBetween(1, 25);

        return $this->faker->randomElement([
            $this->faker->date(),
            $this->faker->randomFloat($randomDecimalLength, 0, 10000),
            $this->faker->randomNumber(30),
            $this->faker->words($randomWordAmount, true)
        ]);
    }
}
