<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_IMAGE_SIZE = 5242880; // 5MB in bytes

    public function uploadImage(UploadedFile $file, string $directory = 'uploads'): string
    {
        $this->validateImage($file);

        $filename = $this->generateFilename($file);
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    public function uploadMultipleImages(array $files, string $directory = 'uploads'): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->uploadImage($file, $directory);
            }
        }

        return $paths;
    }

    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    public function deleteMultipleFiles(array $paths): int
    {
        $deleted = 0;

        foreach ($paths as $path) {
            if ($this->deleteFile($path)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function validateImage(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('File upload failed');
        }

        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            throw new \InvalidArgumentException(
                'Invalid file type. Allowed types: ' . implode(', ', self::ALLOWED_IMAGE_TYPES)
            );
        }

        if ($file->getSize() > self::MAX_IMAGE_SIZE) {
            throw new \InvalidArgumentException(
                'File size exceeds maximum limit of 5MB'
            );
        }
    }

    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    public function getFileUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    public function fileExists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }
}
