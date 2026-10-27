<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use App\Core\Seeder;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Services\QueueService;
use App\Services\RegistrationService;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $existing = (int) Database::connection()->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
        if ($existing > 0) {
            $this->info('Registrations already seeded, skipping.');
            return;
        }

        $patients = Patient::query()->orderBy('id', 'ASC')->limit(8)->get();
        $polyclinics = Polyclinic::query()->where('is_active', '=', 1)->get();

        if ($patients === [] || $polyclinics === []) {
            $this->info('Skipped: patients or polyclinics not found.');
            return;
        }

        $registrationService = new RegistrationService();
        $queueService = new QueueService();
        $registrationIds = [];

        foreach ($patients as $index => $patient) {
            $polyclinic = $polyclinics[$index % count($polyclinics)];
            $doctors = Doctor::activeWithNames((int) $polyclinic['id']);
            $doctorId = $doctors !== [] ? $doctors[0]['id'] : null;

            $registration = $registrationService->create([
                'patient_id' => $patient['id'],
                'doctor_id' => $doctorId,
                'polyclinic_id' => $polyclinic['id'],
                'room_id' => null,
                'appointment_id' => null,
                'payment_type' => ['SELF_PAY', 'BPJS'][array_rand(['SELF_PAY', 'BPJS'])],
                'notes' => null,
            ]);

            $registrationIds[] = $registration['id'];
        }

        foreach (array_slice($registrationIds, 0, 3) as $id) {
            $reg = $registrationService->find($id);
            if (!empty($reg['queue_id'])) {
                $queueService->call($reg['queue_id']);
            }
        }

        $firstReg = $registrationService->find($registrationIds[0]);
        if (!empty($firstReg['queue_id'])) {
            $queueService->complete($firstReg['queue_id']);
        }

        $this->info('Sample registrations & queues created: ' . count($registrationIds));
    }
}
