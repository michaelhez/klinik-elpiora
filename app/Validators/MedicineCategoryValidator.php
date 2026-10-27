<?php

declare(strict_types=1);

namespace App\Validators;

final class MedicineCategoryValidator
{
    public static function rules(): array
    {
        return [
            'name' => 'required|max:150',
            'code' => 'required|max:30|alpha_num',
            'description' => 'max:255',
        ];
    }
}
