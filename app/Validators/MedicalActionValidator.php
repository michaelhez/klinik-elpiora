<?php

declare(strict_types=1);

namespace App\Validators;

final class MedicalActionValidator
{
    public static function rules(): array
    {
        return [
            'category_id' => 'integer',
            'code' => 'required|max:30',
            'name' => 'required|max:150',
            'price' => 'required|numeric',
            'duration' => 'integer',
            'description' => 'max:255',
        ];
    }
}
