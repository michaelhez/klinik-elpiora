<?php

declare(strict_types=1);

namespace App\Validators;

final class PolyclinicValidator
{
    public static function rules(): array
    {
        return [
            'branch_id' => 'required|integer',
            'name' => 'required|max:150',
            'code' => 'required|max:30|alpha_num',
            'description' => 'max:255',
        ];
    }
}
