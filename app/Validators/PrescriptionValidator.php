<?php

declare(strict_types=1);

namespace App\Validators;

final class PrescriptionValidator
{
    public static function rules(): array
    {
        return [
            'medical_record_id' => 'required|integer',
            'notes' => 'max:255',
        ];
    }
}
