<?php

declare(strict_types=1);

namespace App\Validators;

final class MedicalDocumentValidator
{
    public static function sickNoteRules(): array
    {
        return [
            'start_date' => 'required|date',
            'rest_days' => 'required|integer',
            'notes' => 'max:255',
        ];
    }

    public static function referralLetterRules(): array
    {
        return [
            'destination_facility' => 'required|max:150',
            'reason' => 'required|max:255',
        ];
    }

    public static function medicalCertificateRules(): array
    {
        return [
            'purpose' => 'required|max:255',
            'notes' => 'max:255',
        ];
    }
}
