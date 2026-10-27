<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class QueryBuilder
{
    private string $table;
    private array $columns = ['*'];
    private array $wheres = [];
    private array $bindings = [];
    private array $joins = [];
    private array $orders = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private bool $withTrashed = false;
    private bool $hasSoftDeletes = false;

    public function __construct(string $table, bool $hasSoftDeletes = false)
    {
        $this->table = $table;
        $this->hasSoftDeletes = $hasSoftDeletes;
    }

    public function select(array|string $columns): static
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where(string $column, string $operator, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $placeholder = $this->placeholder($column);
        $this->wheres[] = "{$column} {$operator} {$placeholder}";
        $this->bindings[$placeholder] = $value;

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        if ($values === []) {
            $this->wheres[] = '1 = 0';
            return $this;
        }

        $placeholders = [];
        foreach (array_values($values) as $i => $value) {
            $ph = $this->placeholder($column . '_in_' . $i);
            $placeholders[] = $ph;
            $this->bindings[$ph] = $value;
        }

        $this->wheres[] = "{$column} IN (" . implode(', ', $placeholders) . ')';
        return $this;
    }

    public function whereNull(string $column): static
    {
        $this->wheres[] = "{$column} IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): static
    {
        $this->wheres[] = "{$column} IS NOT NULL";
        return $this;
    }

    public function whereLike(string $column, string $value): static
    {
        $placeholder = $this->placeholder($column . '_like');
        $this->wheres[] = "{$column} LIKE {$placeholder}";
        $this->bindings[$placeholder] = '%' . $value . '%';
        return $this;
    }

    public function whereRaw(string $sql, array $bindings = []): static
    {
        $this->wheres[] = $sql;
        foreach ($bindings as $key => $value) {
            $this->bindings[$key] = $value;
        }
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): static
    {
        $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function withTrashed(): static
    {
        $this->withTrashed = true;
        return $this;
    }

    private function placeholder(string $column): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9_]/', '_', $column);
        return ':' . $clean . '_' . count($this->bindings);
    }

    private function baseSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;

        if ($this->joins !== []) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        $wheres = $this->wheres;
        if ($this->hasSoftDeletes && !$this->withTrashed) {
            $wheres[] = "{$this->table}.deleted_at IS NULL";
        }

        if ($wheres !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $wheres);
        }

        if ($this->orders !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    public function get(): array
    {
        $stmt = Database::connection()->prepare($this->baseSql());
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }

    public function count(): int
    {
        $originalColumns = $this->columns;
        $this->columns = ['COUNT(*) as aggregate'];
        $originalLimit = $this->limit;
        $originalOffset = $this->offset;
        $this->limit = null;
        $this->offset = null;

        $stmt = Database::connection()->prepare($this->baseSql());
        $stmt->execute($this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->columns = $originalColumns;
        $this->limit = $originalLimit;
        $this->offset = $originalOffset;

        return (int) ($result['aggregate'] ?? 0);
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $total = $this->count();
        $page = max(1, $page);
        $this->limit($perPage)->offset(($page - 1) * $perPage);

        return [
            'data' => $this->get(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function getSql(): string
    {
        return $this->baseSql();
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}
