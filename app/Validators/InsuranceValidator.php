<?php

declare(strict_types=1);

namespace App\Validators;

final class InsuranceValidator
{
    public static function rules(): array
    {
        return [
            'code' => 'required|max:30',
            'name' => 'required|max:150',
            'type' => 'required|in:BPJS,PRIVATE',
            'contact_person' => 'max:100',
            'phone' => 'max:30',
            'address' => 'max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
