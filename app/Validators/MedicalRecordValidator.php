<?php

declare(strict_types=1);

namespace App\Validators;

final class MedicalRecordValidator
{
    public static function vitalsRules(): array
    {
        return [
            'systolic' => 'integer',
            'diastolic' => 'integer',
            'heart_rate' => 'integer',
            'temperature' => 'numeric',
            'respiratory_rate' => 'integer',
            'oxygen_saturation' => 'integer',
            'weight' => 'numeric',
            'height' => 'numeric',
        ];
    }

    public static function anamnesisRules(): array
    {
        return [
            'chief_complaint' => 'required|max:255',
            'present_illness_history' => 'max:2000',
            'past_medical_history' => 'max:2000',
            'notes' => 'max:2000',
        ];
    }

    public static function physicalExaminationRules(): array
    {
        return [
            'general_condition' => 'max:150',
            'consciousness' => 'max:100',
            'head_neck' => 'max:1000',
            'chest' => 'max:1000',
            'abdomen' => 'max:1000',
            'extremities' => 'max:1000',
            'notes' => 'max:1000',
        ];
    }

    public static function diagnosisRules(): array
    {
        return [
            'icd10_code_id' => 'required|integer',
            'type' => 'required|in:PRIMARY,SECONDARY',
            'notes' => 'max:255',
        ];
    }

    public static function actionRules(): array
    {
        return [
            'medical_action_id' => 'required|integer',
            'quantity' => 'integer',
            'notes' => 'max:255',
        ];
    }

    public static function noteRules(): array
    {
        return [
            'note' => 'required|max:2000',
        ];
    }

    public static function amendRules(): array
    {
        return [
            'section' => 'required|in:anamnesis,physical_examination',
            'reason' => 'required|max:255',
        ];
    }
}
