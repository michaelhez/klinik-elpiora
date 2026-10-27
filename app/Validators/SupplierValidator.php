<?php

declare(strict_types=1);

namespace App\Validators;

final class SupplierValidator
{
    public static function rules(): array
    {
        return [
            'code' => 'required|max:30',
            'name' => 'required|max:150',
            'contact_person' => 'max:100',
            'phone' => 'max:30',
            'email' => 'max:150|email',
            'address' => 'max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
