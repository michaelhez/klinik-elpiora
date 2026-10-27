<?php

declare(strict_types=1);

namespace App\Validators;

final class DoctorValidator
{
    public static function rules(): array
    {
        return [
            'employee_id' => 'required|integer',
            'str_number' => 'max:50',
            'sip_number' => 'max:50',
            'consultation_fee' => 'required|numeric',
            'bio' => 'max:1000',
            'status' => 'in:ACTIVE,INACTIVE',
            'specialization_ids' => 'required',
        ];
    }

    public static function scheduleRules(): array
    {
        return [
            'polyclinic_id' => 'required|integer',
            'room_id' => 'integer',
            'day_of_week' => 'required|integer',
            'start_time' => 'required',
            'end_time' => 'required',
            'quota' => 'integer',
        ];
    }
}
