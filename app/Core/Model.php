<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = true;

    /** @var array<int, string> Columns allowed for mass assignment */
    protected static array $fillable = [];

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::$table, static::$softDeletes);
    }

    public static function all(): array
    {
        return static::query()->orderBy(static::$primaryKey, 'DESC')->get();
    }

    public static function find(int|string $id): ?array
    {
        return static::query()->where(static::$primaryKey, '=', $id)->first();
    }

    public static function findOrFail(int|string $id): array
    {
        $record = static::find($id);

        if ($record === null) {
            throw new \App\Exceptions\NotFoundException(static::class . " record [{$id}] not found.");
        }

        return $record;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        return static::query()->where($column, '=', $value)->first();
    }

    public static function create(array $data): int|string
    {
        $data = static::filterFillable($data);

        if (static::$timestamps) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(static::bindData($data));

        return Database::connection()->lastInsertId();
    }

    public static function update(int|string $id, array $data): bool
    {
        $data = static::filterFillable($data);

        if (static::$timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $assignments = [];
        foreach (array_keys($data) as $column) {
            $assignments[] = "{$column} = :{$column}";
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :__pk',
            static::$table,
            implode(', ', $assignments),
            static::$primaryKey
        );

        $bindings = static::bindData($data);
        $bindings['__pk'] = $id;

        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute($bindings);
    }

    public static function delete(int|string $id): bool
    {
        if (static::$softDeletes) {
            return static::update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        }

        $sql = sprintf('DELETE FROM %s WHERE %s = :id', static::$table, static::$primaryKey);
        $stmt = Database::connection()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public static function restore(int|string $id): bool
    {
        return static::update($id, ['deleted_at' => null]);
    }

    protected static function filterFillable(array $data): array
    {
        if (static::$fillable === []) {
            return $data;
        }

        return array_intersect_key($data, array_flip(static::$fillable));
    }

    private static function bindData(array $data): array
    {
        $bindings = [];
        foreach ($data as $key => $value) {
            $bindings[$key] = is_bool($value) ? (int) $value : $value;
        }
        return $bindings;
    }

    public static function table(): string
    {
        return static::$table;
    }
}
