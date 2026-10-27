<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineUnit;

final class MedicineService
{
    public function __construct(
        private readonly AuditService $auditService = new AuditService(),
        private readonly InventoryService $inventoryService = new InventoryService(),
    ) {
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        $conditions = ['m.deleted_at IS NULL'];
        $bindings = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(m.name LIKE :search1 OR m.code LIKE :search2 OR m.generic_name LIKE :search3)';
            $needle = '%' . $filters['search'] . '%';
            $bindings['search1'] = $needle;
            $bindings['search2'] = $needle;
            $bindings['search3'] = $needle;
        }

        if (!empty($filters['category_id'])) {
            $conditions[] = 'm.category_id = :category_id';
            $bindings['category_id'] = $filters['category_id'];
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $countStmt = Database::connection()->prepare("SELECT COUNT(*) FROM medicines m {$where}");
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT m.*, c.name AS category_name, u.name AS unit_name,
                    COALESCE((SELECT SUM(b.quantity) FROM medicine_batches b WHERE b.medicine_id = m.id AND b.expired_date >= CURDATE()), 0) AS current_stock
                FROM medicines m
                LEFT JOIN medicine_categories c ON c.id = m.category_id
                LEFT JOIN medicine_units u ON u.id = m.unit_id
                {$where}
                ORDER BY m.name ASC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function find(int|string $id): array
    {
        $medicine = Medicine::findWithRelations($id);

        if ($medicine === null) {
            throw new NotFoundException('Data obat tidak ditemukan.');
        }

        return $medicine;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);
        $this->validateRelations($data);

        $data['status'] = $data['status'] ?? 'ACTIVE';
        $data = $this->cleanFields($data);

        $id = Medicine::create($data);
        $this->auditService->log('CREATE', 'medicine', 'medicines', $id, null, $data);

        return $this->find($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);
        $this->validateRelations($data);

        $data = $this->cleanFields($data);

        Medicine::update($id, $data);
        $this->auditService->log('UPDATE', 'medicine', 'medicines', $id, $existing, $data);

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $usage = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM prescription_items WHERE medicine_id = ' . (int) $id)
            ->fetchColumn();

        if ($usage > 0) {
            throw new ValidationException(['code' => ['Obat tidak dapat dihapus karena sudah pernah diresepkan.']]);
        }

        Medicine::delete($id);
        $this->auditService->log('DELETE', 'medicine', 'medicines', $id, $existing, null);
    }

    public function addBatch(int|string $medicineId, array $data): array
    {
        $this->find($medicineId);

        return $this->inventoryService->receiveStock(
            $medicineId,
            $data['branch_id'],
            $data['batch_number'],
            $data['expired_date'],
            (int) $data['quantity'],
            (float) $data['purchase_price'],
            'PURCHASE',
            'manual',
            null,
            'Input stok awal/manual'
        );
    }

    private function validateRelations(array $data): void
    {
        if (!empty($data['category_id']) && MedicineCategory::find($data['category_id']) === null) {
            throw new ValidationException(['category_id' => ['Kategori tidak valid.']]);
        }

        if (!empty($data['unit_id']) && MedicineUnit::find($data['unit_id']) === null) {
            throw new ValidationException(['unit_id' => ['Satuan tidak valid.']]);
        }
    }

    private function cleanFields(array $data): array
    {
        $data['category_id'] = $data['category_id'] ?: null;
        $data['unit_id'] = $data['unit_id'] ?: null;
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;

        return $data;
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Medicine::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode obat sudah digunakan.']]);
        }
    }
}
