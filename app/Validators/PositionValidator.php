<?php

declare(strict_types=1);

namespace App\Validators;

final class PositionValidator
{
    public static function rules(): array
    {
        return [
            'department_id' => 'integer',
            'name' => 'required|max:150',
            'code' => 'required|max:30|alpha_num',
            'level' => 'max:50',
            'description' => 'max:255',
        ];
    }
}
