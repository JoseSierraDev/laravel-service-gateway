<?php

namespace LaravelServiceGateway\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseValidator
{
    /**
     * Returns the validation rules.
     *
     * @return array
     */
    abstract public static function rules(): array;

    /**
     * Validates the given data using the defined rules.
     *
     * @param array $data
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validate(array $data): array
    {
        $rules = call_user_func([static::class, 'rules']);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
