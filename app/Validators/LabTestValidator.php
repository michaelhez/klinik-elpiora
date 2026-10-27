<?php

declare(strict_types=1);

namespace App\Validators;

final class LabTestValidator
{
    public static function rules(): array
    {
        return [
            'code' => 'required|max:30',
            'name' => 'required|max:150',
            'category' => 'max:100',
            'sample_type' => 'max:50',
            'price' => 'required|numeric',
            'description' => 'max:255',
        ];
    }

    public static function parameterRules(): array
    {
        return [
            'name' => 'required|max:150',
            'unit' => 'max:30',
            'value_type' => 'required|in:NUMERIC,TEXT',
            'reference_min' => 'numeric',
            'reference_max' => 'numeric',
            'reference_text' => 'max:150',
        ];
    }
}
