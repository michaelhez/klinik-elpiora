<?php

declare(strict_types=1);

namespace App\Validators;

final class EmployeeValidator
{
    public static function rules(): array
    {
        return [
            'branch_id' => 'required|integer',
            'department_id' => 'integer',
            'position_id' => 'integer',
            'name' => 'required|max:150',
            'gender' => 'required|in:MALE,FEMALE',
            'nik' => 'max:20',
            'phone' => 'max:30',
            'email' => 'email|max:150',
            'address' => 'max:255',
            'birth_place' => 'max:100',
            'birth_date' => 'date',
            'join_date' => 'date',
            'status' => 'in:ACTIVE,INACTIVE,RESIGNED',
        ];
    }

    public static function emergencyContactRules(): array
    {
        return [
            'name' => 'required|max:150',
            'relationship' => 'required|max:50',
            'phone' => 'required|max:30',
            'address' => 'max:255',
        ];
    }
}
