<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Corporate;

final class CorporateService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = Corporate::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $corporate = Corporate::find($id);

        if ($corporate === null) {
            throw new NotFoundException('Perusahaan rekanan tidak ditemukan.');
        }

        return $corporate;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);

        $id = Corporate::create($data);
        $this->auditService->log('CREATE', 'corporate', 'corporates', $id, null, $data);

        return Corporate::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        Corporate::update($id, $data);
        $this->auditService->log('UPDATE', 'corporate', 'corporates', $id, $existing, $data);

        return Corporate::findOrFail($id);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Corporate::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode perusahaan sudah digunakan.']]);
        }
    }
}
