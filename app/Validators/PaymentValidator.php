<?php

declare(strict_types=1);

namespace App\Validators;

final class PaymentValidator
{
    public static function payRules(): array
    {
        return [
            'method' => 'required|in:CASH,TRANSFER,DEBIT,CREDIT,INSURANCE,CORPORATE',
            'amount' => 'required|numeric',
            'reference_number' => 'max:100',
            'notes' => 'max:255',
        ];
    }

    public static function refundRules(): array
    {
        return [
            'amount' => 'required|numeric',
            'notes' => 'required|max:255',
        ];
    }
}
