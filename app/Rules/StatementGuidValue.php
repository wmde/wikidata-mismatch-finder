<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class StatementGuidValue implements Rule, DataAwareRule
{
    /** @var array */
    protected $data = [];

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $itemId = strtoupper(explode('$', $value, 2)[0]);
        return $itemId === $this->data['item_id'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.statement_guid');
    }
}
