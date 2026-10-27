<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\QueueService;

class QueueController extends Controller
{
    public function __construct(private readonly QueueService $service = new QueueService())
    {
    }

    public function call(Request $request, string $id): never
    {
        $this->respond($id, fn () => $this->service->call($id), 'Antrian berhasil dipanggil.');
    }

    public function recall(Request $request, string $id): never
    {
        $this->respond($id, fn () => $this->service->recall($id), 'Antrian berhasil dipanggil ulang.');
    }

    public function complete(Request $request, string $id): never
    {
        $this->respond($id, fn () => $this->service->complete($id), 'Antrian berhasil diselesaikan.');
    }

    public function skip(Request $request, string $id): never
    {
        $this->respond($id, fn () => $this->service->skip($id), 'Antrian berhasil dilewati.');
    }

    public function boardData(Request $request): never
    {
        $sql = "SELECT poly.id AS polyclinic_id,
                    (SELECT q1.queue_number FROM queues q1 WHERE q1.polyclinic_id = poly.id AND q1.queue_date = CURDATE() AND q1.status IN ('CALLED','IN_SERVICE') ORDER BY q1.called_at DESC LIMIT 1) AS current,
                    (SELECT q2.queue_number FROM queues q2 WHERE q2.polyclinic_id = poly.id AND q2.queue_date = CURDATE() AND q2.status = 'WAITING' ORDER BY q2.id ASC LIMIT 1) AS next
                FROM polyclinics poly
                WHERE poly.is_active = 1";

        $data = Database::connection()->query($sql)->fetchAll();

        $this->success('OK', $data);
    }

    private function respond(string $id, callable $action, string $message): never
    {
        try {
            $queue = $action();
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Aksi tidak valid.', ['errors' => $e->errors()], 422);
        }

        $this->success($message, $queue);
    }
}
