<?php

declare(strict_types=1);

namespace App\Validators;

final class UserValidator
{
    public static function createRules(): array
    {
        return [
            'name' => 'required|max:150',
            'email' => 'required|email|max:150',
            'username' => 'max:100',
            'phone' => 'max:30',
            'password' => 'required|min:8',
            'is_active' => 'integer',
        ];
    }

    public static function updateRules(): array
    {
        return [
            'name' => 'required|max:150',
            'email' => 'required|email|max:150',
            'username' => 'max:100',
            'phone' => 'max:30',
            'password' => 'min:8',
            'is_active' => 'integer',
        ];
    }

    public static function resetPasswordRules(): array
    {
        return [
            'password' => 'required|min:8',
        ];
    }
}
