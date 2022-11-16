<?php

namespace Database\Factories;

use App\Models\Mismatch;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;
use ValueError;

class MismatchFactory extends Factory
{
    private const PROPERTIES = [
        'P580' => 'time',
        'P582' => 'time',
        'P585' => 'time',
        'P50' => 'wikibase-item',
        'P86' => 'wikibase-item',
        'P170' => 'wikibase-item',
        'P225' => 'string',
        'P742' => 'string',
        'P2093' => 'string',
    ];

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
            'item_id' => function (array $attributes) {
                return strtoupper(explode('$', $attributes['statement_guid'], 2)[0]);
            },
            'statement_guid' => $this->getRandomItemId() . '$' . $this->faker->uuid(),
            'property_id' => $this->faker->randomElement(array_keys(self::PROPERTIES)),
            'wikidata_value' => function (array $attributes) {
                $propertyId = $attributes['property_id'];
                if (!array_key_exists($propertyId, self::PROPERTIES)) {
                    throw new ValueError("Unknown property {$propertyId}, " .
                        'you have to specify an explicit wikidata_value');
                }
                return $this->getRandomValueForDatatype(self::PROPERTIES[$propertyId]);
            },
            'meta_wikidata_value' => function (array $attributes) {
                $propertyId = $attributes['property_id'];
                if (!array_key_exists($propertyId, self::PROPERTIES)) {
                    throw new ValueError("Unknown property {$propertyId}");
                }

                $datatype = self::PROPERTIES[$propertyId];
                if ($datatype == 'time') {
                    $randomNumber = $this->faker->optional(0.7, 0)->randomNumber();
                    if ($randomNumber != 0) {
                        return 'Q'.$randomNumber;
                    }
                }

                return null;
            },
            'external_value'=> $this->getRandomValue(),
            'external_url' => $this->faker->optional(0.6)->url()
        ];
    }

    /** Select a random property of datatype "time". */
    public function datatypeTime(): self
    {
        return $this->state([
            'property_id' => $this->getRandomPropertyIdOfDatatype('time'),
        ]);
    }

    /** Select a random property of datatype "wikibase-item". */
    public function datatypeWikibaseItem(): self
    {
        return $this->state([
            'property_id' => $this->getRandomPropertyIdOfDatatype('wikibase-item'),
        ]);
    }

    /** Select a random property of datatype "string". */
    public function datatypeString(): self
    {
        return $this->state([
            'property_id' => $this->getRandomPropertyIdOfDatatype('string'),
        ]);
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

    private function getRandomValueForDatatype(string $datatype)
    {
        switch ($datatype) {
            case 'time':
                return $this->faker->date();
            case 'wikibase-item':
                return $this->getRandomItemId();
            case 'string':
                $randomWordAmount = $this->faker->numberBetween(1, 5);
                return $this->faker->words($randomWordAmount, true);
            default:
                throw new InvalidArgumentException("Unknown datatype $datatype");
        }
    }

    private function getRandomItemId(): string
    {
        return 'Q' . $this->faker->numberBetween(1, 10000);
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

    private function getRandomPropertyIdOfDatatype(string $datatype)
    {
        return $this->faker->randomElement(array_filter(
            array_keys(self::PROPERTIES),
            static function ($propertyId) use ($datatype) {
                return self::PROPERTIES[$propertyId] === $datatype;
            }
        ));
    }
}
