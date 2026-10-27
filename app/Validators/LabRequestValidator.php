<?php

declare(strict_types=1);

namespace App\Validators;

final class LabRequestValidator
{
    public static function rules(): array
    {
        return [
            'medical_record_id' => 'required|integer',
            'lab_test_ids' => 'required',
            'notes' => 'max:255',
        ];
    }

    public static function sampleRules(): array
    {
        return [
            'sample_type' => 'required|max:50',
            'notes' => 'max:255',
        ];
    }
}
