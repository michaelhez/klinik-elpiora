<?php

declare(strict_types=1);

namespace App\Validators;

final class PurchaseOrderValidator
{
    public static function rules(): array
    {
        return [
            'supplier_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'order_date' => 'date',
            'expected_date' => 'date',
            'notes' => 'max:255',
        ];
    }

    public static function receiveRules(): array
    {
        return [
            'notes' => 'max:255',
        ];
    }
}
