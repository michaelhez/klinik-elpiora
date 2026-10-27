<?php

declare(strict_types=1);

namespace App\Validators;

final class AppointmentValidator
{
    public static function rules(): array
    {
        return [
            'patient_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'polyclinic_id' => 'required|integer',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'notes' => 'max:255',
        ];
    }
}
