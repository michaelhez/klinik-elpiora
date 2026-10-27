<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Supplier;

final class SupplierService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = Supplier::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $supplier = Supplier::find($id);

        if ($supplier === null) {
            throw new NotFoundException('Pemasok tidak ditemukan.');
        }

        return $supplier;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);

        $id = Supplier::create($data);
        $this->auditService->log('CREATE', 'supplier', 'suppliers', $id, null, $data);

        return Supplier::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        Supplier::update($id, $data);
        $this->auditService->log('UPDATE', 'supplier', 'suppliers', $id, $existing, $data);

        return Supplier::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM purchase_orders WHERE supplier_id = :id');
        $stmt->execute(['id' => $id]);

        if ((int) $stmt->fetchColumn() > 0) {
            throw new ValidationException(['code' => ['Pemasok tidak dapat dihapus karena memiliki riwayat pembelian.']]);
        }

        Supplier::delete($id);
        $this->auditService->log('DELETE', 'supplier', 'suppliers', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Supplier::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode pemasok sudah digunakan.']]);
        }
    }
}
