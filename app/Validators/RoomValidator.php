<?php

declare(strict_types=1);

namespace App\Validators;

final class RoomValidator
{
    public static function rules(): array
    {
        return [
            'branch_id' => 'required|integer',
            'polyclinic_id' => 'integer',
            'name' => 'required|max:150',
            'code' => 'required|max:30|alpha_num',
            'floor' => 'max:30',
            'capacity' => 'required|integer',
        ];
    }
}
