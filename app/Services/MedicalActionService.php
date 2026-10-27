<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\MedicalAction;
use App\Models\MedicalActionCategory;

final class MedicalActionService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $sql = 'SELECT a.*, c.name AS category_name FROM medical_actions a
                LEFT JOIN medical_action_categories c ON c.id = a.category_id
                WHERE a.deleted_at IS NULL';

        if ($search !== '') {
            $sql .= ' AND a.name LIKE :search';
        }

        $sql .= ' ORDER BY a.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($search !== '' ? ['search' => "%{$search}%"] : []);

        return $stmt->fetchAll();
    }

    public function find(int|string $id): array
    {
        $action = MedicalAction::find($id);

        if ($action === null) {
            throw new NotFoundException('Tindakan medis tidak ditemukan.');
        }

        return $action;
    }

    public function create(array $data): array
    {
        if (!empty($data['category_id']) && MedicalActionCategory::find($data['category_id']) === null) {
            throw new ValidationException(['category_id' => ['Kategori tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code']);
        $data['status'] = $data['status'] ?? 'ACTIVE';
        $data['category_id'] = $data['category_id'] ?: null;
        $data['duration'] = $data['duration'] ?: null;

        $id = MedicalAction::create($data);
        $this->auditService->log('CREATE', 'medical_action', 'medical_actions', $id, null, $data);

        return MedicalAction::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);
        $data['category_id'] = $data['category_id'] ?: null;
        $data['duration'] = $data['duration'] ?: null;

        MedicalAction::update($id, $data);
        $this->auditService->log('UPDATE', 'medical_action', 'medical_actions', $id, $existing, $data);

        return MedicalAction::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $usage = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM medical_record_actions WHERE medical_action_id = ' . (int) $id)
            ->fetchColumn();

        if ($usage > 0) {
            throw new ValidationException(['code' => ['Tindakan tidak dapat dihapus karena sudah pernah digunakan pada rekam medis.']]);
        }

        MedicalAction::delete($id);
        $this->auditService->log('DELETE', 'medical_action', 'medical_actions', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = MedicalAction::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode tindakan sudah digunakan.']]);
        }
    }
}
