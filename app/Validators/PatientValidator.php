<?php

declare(strict_types=1);

namespace App\Validators;

final class PatientValidator
{
    public static function rules(): array
    {
        return [
            'name' => 'required|max:150',
            'gender' => 'required|in:MALE,FEMALE',
            'blood_type' => 'in:A,B,AB,O,UNKNOWN',
            'nik' => 'max:20',
            'phone' => 'required|max:30',
            'email' => 'email|max:150',
            'address' => 'max:255',
            'city' => 'max:100',
            'birth_place' => 'max:100',
            'birth_date' => 'date',
            'marital_status' => 'in:SINGLE,MARRIED,DIVORCED,WIDOWED',
            'occupation' => 'max:100',
            'status' => 'in:ACTIVE,INACTIVE',
        ];
    }

    public static function allergyRules(): array
    {
        return [
            'allergen' => 'required|max:150',
            'severity' => 'required|in:MILD,MODERATE,SEVERE',
            'reaction' => 'max:255',
            'notes' => 'max:255',
        ];
    }

    public static function medicalHistoryRules(): array
    {
        return [
            'condition_name' => 'required|max:150',
            'diagnosed_date' => 'date',
            'status' => 'in:ONGOING,RESOLVED',
            'notes' => 'max:255',
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
