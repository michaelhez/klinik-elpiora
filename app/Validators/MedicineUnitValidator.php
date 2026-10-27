<?php

declare(strict_types=1);

namespace App\Validators;

final class MedicineUnitValidator
{
    public static function rules(): array
    {
        return [
            'name' => 'required|max:50',
            'code' => 'required|max:20|alpha_num',
        ];
    }
}
