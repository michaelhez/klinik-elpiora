<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ValidationException;

final class Validator
{
    private array $errors = [];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules e.g. ['email' => 'required|email', 'password' => 'required|min:8']
     */
    public function __construct(private readonly array $data, private readonly array $rules, private readonly array $messages = [])
    {
    }

    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new self($data, $rules, $messages);
    }

    public function validate(): array
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        if ($this->errors !== []) {
            throw new ValidationException($this->errors);
        }

        return $this->data;
    }

    public function fails(): bool
    {
        try {
            $this->validate();
            return false;
        } catch (ValidationException) {
            return true;
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

        $label = ucwords(str_replace('_', ' ', $field));

        $isValid = match ($name) {
            'required' => $value !== null && $value !== '',
            'email' => $value === null || $value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'min' => $value === null || $value === '' || mb_strlen((string) $value) >= (int) $param,
            'max' => $value === null || $value === '' || mb_strlen((string) $value) <= (int) $param,
            'numeric' => $value === null || $value === '' || is_numeric($value),
            'integer' => $value === null || $value === '' || filter_var($value, FILTER_VALIDATE_INT) !== false,
            'alpha_num' => $value === null || $value === '' || ctype_alnum(str_replace(['_', '-', ' '], '', (string) $value)),
            'confirmed' => $value === ($this->data[$field . '_confirmation'] ?? null),
            'in' => $value === null || $value === '' || in_array($value, explode(',', (string) $param), true),
            'date' => $value === null || $value === '' || strtotime((string) $value) !== false,
            'boolean' => in_array($value, [true, false, 0, 1, '0', '1'], true),
            default => true,
        };

        if (!$isValid) {
            $this->errors[$field][] = $this->messages[$field . '.' . $name]
                ?? $this->defaultMessage($label, $name, $param);
        }
    }

    private function defaultMessage(string $label, string $rule, ?string $param): string
    {
        return match ($rule) {
            'required' => "{$label} wajib diisi.",
            'email' => "{$label} harus berupa alamat email yang valid.",
            'min' => "{$label} minimal {$param} karakter.",
            'max' => "{$label} maksimal {$param} karakter.",
            'numeric' => "{$label} harus berupa angka.",
            'integer' => "{$label} harus berupa bilangan bulat.",
            'alpha_num' => "{$label} hanya boleh berisi huruf dan angka.",
            'confirmed' => "Konfirmasi {$label} tidak cocok.",
            'in' => "{$label} tidak valid.",
            'date' => "{$label} harus berupa tanggal yang valid.",
            'boolean' => "{$label} harus bernilai benar atau salah.",
            default => "{$label} tidak valid.",
        };
    }
}
