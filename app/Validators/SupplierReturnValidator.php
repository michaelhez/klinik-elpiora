<?php

declare(strict_types=1);

namespace App\Validators;

final class SupplierReturnValidator
{
    public static function rules(): array
    {
        return [
            'supplier_id' => 'required|integer',
            'goods_receipt_id' => 'integer',
            'reason' => 'required|max:255',
        ];
    }
}
