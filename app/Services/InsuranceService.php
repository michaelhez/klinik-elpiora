<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Insurance;

final class InsuranceService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = Insurance::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $insurance = Insurance::find($id);

        if ($insurance === null) {
            throw new NotFoundException('Asuransi tidak ditemukan.');
        }

        return $insurance;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);

        $id = Insurance::create($data);
        $this->auditService->log('CREATE', 'insurance', 'insurances', $id, null, $data);

        return Insurance::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        Insurance::update($id, $data);
        $this->auditService->log('UPDATE', 'insurance', 'insurances', $id, $existing, $data);

        return Insurance::findOrFail($id);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Insurance::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode asuransi sudah digunakan.']]);
        }
    }
}
