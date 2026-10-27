<?php

declare(strict_types=1);

namespace App\Validators;

final class MedicineValidator
{
    public static function rules(): array
    {
        return [
            'category_id' => 'integer',
            'unit_id' => 'integer',
            'code' => 'required|max:30',
            'name' => 'required|max:150',
            'generic_name' => 'max:150',
            'brand_name' => 'max:150',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'minimum_stock' => 'integer',
            'description' => 'max:255',
        ];
    }

    public static function batchRules(): array
    {
        return [
            'branch_id' => 'required|integer',
            'batch_number' => 'required|max:50',
            'expired_date' => 'required|date',
            'quantity' => 'required|integer',
            'purchase_price' => 'required|numeric',
        ];
    }

    public static function adjustRules(): array
    {
        return [
            'medicine_batch_id' => 'required|integer',
            'adjustment_type' => 'required|in:ADD,REMOVE',
            'quantity' => 'required|integer',
            'reason' => 'required|max:255',
        ];
    }

    public static function transferRules(): array
    {
        return [
            'medicine_batch_id' => 'required|integer',
            'to_branch_id' => 'required|integer',
            'quantity' => 'required|integer',
            'notes' => 'max:255',
        ];
    }
}
