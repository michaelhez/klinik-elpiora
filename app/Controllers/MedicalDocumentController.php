<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Models\Clinic;
use App\Services\MedicalDocumentService;
use App\Services\PdfService;
use App\Validators\MedicalDocumentValidator;

class MedicalDocumentController extends Controller
{
    private const TEMPLATES = [
        'SICK_NOTE' => 'documents.sick_note',
        'REFERRAL_LETTER' => 'documents.referral_letter',
        'MEDICAL_CERTIFICATE' => 'documents.medical_certificate',
    ];

    public function __construct(
        private readonly MedicalDocumentService $service = new MedicalDocumentService(),
        private readonly PdfService $pdfService = new PdfService(),
    ) {
    }

    public function store(Request $request, string $medicalRecordId): never
    {
        $type = (string) $request->input('type');

        $rules = match ($type) {
            'SICK_NOTE' => MedicalDocumentValidator::sickNoteRules(),
            'REFERRAL_LETTER' => MedicalDocumentValidator::referralLetterRules(),
            'MEDICAL_CERTIFICATE' => MedicalDocumentValidator::medicalCertificateRules(),
            default => null,
        };

        if ($rules === null) {
            $this->fail('Jenis dokumen tidak valid.', [], 422);
        }

        $data = $this->validate($request, $rules);

        try {
            $document = match ($type) {
                'SICK_NOTE' => $this->service->generateSickNote($medicalRecordId, $data),
                'REFERRAL_LETTER' => $this->service->generateReferralLetter($medicalRecordId, $data),
                'MEDICAL_CERTIFICATE' => $this->service->generateMedicalCertificate($medicalRecordId, $data),
            };
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Dokumen tidak dapat diterbitkan.', ['errors' => $e->errors()], 422);
        }

        $this->success('Dokumen berhasil diterbitkan.', $document, 201);
    }

    public function pdf(Request $request, string $id): never
    {
        $document = $this->service->find($id);
        $template = self::TEMPLATES[$document['type']] ?? null;

        if ($template === null) {
            $this->fail('Jenis dokumen tidak dikenal.', [], 422);
        }

        $clinic = Clinic::query()->orderBy('id', 'ASC')->first() ?? [];

        $pdf = $this->pdfService->render($template, ['document' => $document, 'clinic' => $clinic]);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $document['document_number'] . '.pdf"');
        echo $pdf;
        exit;
    }
}
