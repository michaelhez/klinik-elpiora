<?php

declare(strict_types=1);

namespace App\Validators;

final class ClinicValidator
{
    public static function rules(): array
    {
        return [
            'name' => 'required|max:150',
            'code' => 'required|max:30|alpha_num',
            'email' => 'email|max:150',
            'phone' => 'max:30',
            'address' => 'max:255',
        ];
    }
}
