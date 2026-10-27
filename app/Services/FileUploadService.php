<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;

final class FileUploadService
{
    private const IMAGE_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const DOCUMENT_MIME_EXTENSIONS = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /**
     * Store a validated image publicly (e.g. profile photo) and return its relative path.
     *
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file
     */
    public function storePhoto(array $file, string $directory, int $maxSizeBytes = 2097152): string
    {
        $extension = $this->validate($file, self::IMAGE_MIME_EXTENSIONS, $maxSizeBytes);

        $targetDir = public_path('uploads/' . trim($directory, '/'));
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename);

        return trim($directory, '/') . '/' . $filename;
    }

    /**
     * Store a validated document privately (outside the public web root) and return its relative path.
     *
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file
     */
    public function storeDocument(array $file, string $directory, int $maxSizeBytes = 5242880): string
    {
        $extension = $this->validate($file, self::DOCUMENT_MIME_EXTENSIONS, $maxSizeBytes);

        $targetDir = storage_path('documents/' . trim($directory, '/'));
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename);

        return trim($directory, '/') . '/' . $filename;
    }

    public function deletePhoto(string $relativePath): void
    {
        $path = public_path('uploads/' . ltrim($relativePath, '/'));
        if (is_file($path)) {
            unlink($path);
        }
    }

    public function deleteDocument(string $relativePath): void
    {
        $path = storage_path('documents/' . ltrim($relativePath, '/'));
        if (is_file($path)) {
            unlink($path);
        }
    }

    public function documentAbsolutePath(string $relativePath): string
    {
        return storage_path('documents/' . ltrim($relativePath, '/'));
    }

    /**
     * @param array<string, string> $allowedMimeExtensions
     * @return string The validated file extension.
     */
    private function validate(array $file, array $allowedMimeExtensions, int $maxSizeBytes): string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new ValidationException(['file' => ['Berkas gagal diunggah.']]);
        }

        if ($file['size'] > $maxSizeBytes) {
            $maxMb = round($maxSizeBytes / 1048576, 1);
            throw new ValidationException(['file' => ["Ukuran berkas maksimal {$maxMb} MB."]]);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMimeExtensions[$mimeType])) {
            throw new ValidationException(['file' => ['Tipe berkas tidak diizinkan.']]);
        }

        $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
        $expectedExtension = $allowedMimeExtensions[$mimeType];

        $normalizedExtension = $extension === 'jpeg' ? 'jpg' : $extension;
        if ($normalizedExtension !== $expectedExtension) {
            throw new ValidationException(['file' => ['Ekstensi berkas tidak sesuai dengan tipe berkas.']]);
        }

        return $expectedExtension;
    }
}
