<?php

declare(strict_types=1);

namespace App\Validators;

final class CorporateValidator
{
    public static function rules(): array
    {
        return [
            'code' => 'required|max:30',
            'name' => 'required|max:150',
            'pic_name' => 'max:100',
            'phone' => 'max:30',
            'email' => 'max:150|email',
            'address' => 'max:255',
            'credit_limit' => 'numeric',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
