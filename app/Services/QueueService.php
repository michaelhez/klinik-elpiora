<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Queue;
use App\Models\QueueStatusHistory;

final class QueueService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function todayForPolyclinic(int|string $polyclinicId): array
    {
        return Queue::todayForPolyclinic($polyclinicId);
    }

    public function todayAll(): array
    {
        return Queue::todayAll();
    }

    public function currentlyServing(): array
    {
        return Queue::currentlyServingByPolyclinic();
    }

    public function find(int|string $id): array
    {
        $queue = Queue::find($id);

        if ($queue === null) {
            throw new NotFoundException('Data antrian tidak ditemukan.');
        }

        return $queue;
    }

    public function call(int|string $id): array
    {
        $queue = $this->find($id);

        if ($queue['status'] !== 'WAITING') {
            throw new ValidationException(['status' => ['Hanya antrian berstatus menunggu yang dapat dipanggil.']]);
        }

        return $this->transition($id, 'CALLED', ['called_at' => date('Y-m-d H:i:s')]);
    }

    public function recall(int|string $id): array
    {
        $queue = $this->find($id);

        if ($queue['status'] !== 'CALLED') {
            throw new ValidationException(['status' => ['Hanya antrian yang sedang dipanggil yang dapat dipanggil ulang.']]);
        }

        Queue::update($id, ['called_at' => date('Y-m-d H:i:s')]);
        $this->logHistory($id, 'CALLED', 'Dipanggil ulang');

        return $this->find($id);
    }

    public function complete(int|string $id): array
    {
        $queue = $this->find($id);

        if (!in_array($queue['status'], ['CALLED', 'IN_SERVICE'], true)) {
            throw new ValidationException(['status' => ['Antrian belum dipanggil.']]);
        }

        return $this->transition($id, 'COMPLETED', ['completed_at' => date('Y-m-d H:i:s')]);
    }

    public function skip(int|string $id): array
    {
        $queue = $this->find($id);

        if (!in_array($queue['status'], ['WAITING', 'CALLED'], true)) {
            throw new ValidationException(['status' => ['Antrian ini tidak dapat dilewati.']]);
        }

        return $this->transition($id, 'SKIPPED');
    }

    private function transition(int|string $id, string $status, array $extra = []): array
    {
        $existing = $this->find($id);

        Queue::update($id, array_merge(['status' => $status], $extra));
        $this->logHistory($id, $status);

        $this->auditService->log('UPDATE', 'queue', 'queues', $id, $existing, ['status' => $status]);

        return $this->find($id);
    }

    private function logHistory(int|string $queueId, string $status, ?string $notes = null): void
    {
        QueueStatusHistory::create([
            'queue_id' => $queueId,
            'status' => $status,
            'notes' => $notes,
            'changed_by' => Session::get('auth_user')['id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
