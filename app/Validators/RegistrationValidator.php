<?php

declare(strict_types=1);

namespace App\Validators;

final class RegistrationValidator
{
    public static function rules(): array
    {
        return [
            'patient_id' => 'required|integer',
            'polyclinic_id' => 'required|integer',
            'doctor_id' => 'integer',
            'room_id' => 'integer',
            'appointment_id' => 'integer',
            'payment_type' => 'required|in:SELF_PAY,BPJS,INSURANCE,CORPORATE',
            'notes' => 'max:255',
        ];
    }
}
