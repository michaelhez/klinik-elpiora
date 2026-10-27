<?php

declare(strict_types=1);

namespace App\Validators;

final class Icd10Validator
{
    public static function rules(): array
    {
        return [
            'code' => 'required|max:15',
            'description' => 'required|max:255',
            'category' => 'max:150',
        ];
    }
}
