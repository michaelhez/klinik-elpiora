<?php

declare(strict_types=1);

namespace App\Validators;

final class RoleValidator
{
    public static function rules(): array
    {
        return [
            'name' => 'required|max:100',
            'description' => 'max:255',
        ];
    }
}
