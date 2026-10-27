<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Branch;
use App\Models\Polyclinic;
use App\Models\Room;

final class RoomService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $sql = 'SELECT r.*, b.name AS branch_name, p.name AS polyclinic_name FROM rooms r
                INNER JOIN branches b ON b.id = r.branch_id
                LEFT JOIN polyclinics p ON p.id = r.polyclinic_id
                WHERE r.deleted_at IS NULL';

        if ($search !== '') {
            $sql .= ' AND r.name LIKE :search';
        }

        $sql .= ' ORDER BY r.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($search !== '' ? ['search' => "%{$search}%"] : []);

        return $stmt->fetchAll();
    }

    public function find(int|string $id): array
    {
        $room = Room::find($id);

        if ($room === null) {
            throw new NotFoundException('Data ruangan tidak ditemukan.');
        }

        return $room;
    }

    public function create(array $data): array
    {
        if (Branch::find($data['branch_id']) === null) {
            throw new ValidationException(['branch_id' => ['Cabang tidak valid.']]);
        }

        if (!empty($data['polyclinic_id']) && Polyclinic::find($data['polyclinic_id']) === null) {
            throw new ValidationException(['polyclinic_id' => ['Poliklinik tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code']);
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['polyclinic_id'] = $data['polyclinic_id'] ?: null;

        $id = Room::create($data);
        $this->auditService->log('CREATE', 'room', 'rooms', $id, null, $data);

        return Room::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);

        if (Branch::find($data['branch_id']) === null) {
            throw new ValidationException(['branch_id' => ['Cabang tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code'], $id);
        $data['polyclinic_id'] = $data['polyclinic_id'] ?: null;

        Room::update($id, $data);
        $this->auditService->log('UPDATE', 'room', 'rooms', $id, $existing, $data);

        return Room::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);
        Room::delete($id);
        $this->auditService->log('DELETE', 'room', 'rooms', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Room::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode ruangan sudah digunakan.']]);
        }
    }
}
