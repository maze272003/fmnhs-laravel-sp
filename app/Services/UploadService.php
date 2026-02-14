<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    /**
     * Allowed MIME types for different upload contexts.
     */
    protected array $allowedMimeTypes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'video' => ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'],
        'conference' => ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4'],
    ];

    /**
     * Maximum file sizes in kilobytes for different contexts.
     */
    protected array $maxFileSizes = [
        'image' => 10240, // 10MB
        'document' => 20480, // 20MB
        'video' => 102400, // 100MB
        'conference' => 40960, // 40MB
    ];

    /**
     * Upload a file to the specified disk and directory.
     *
     * @param UploadedFile $file The uploaded file
     * @param string $directory The target directory
     * @param string $context The upload context (image, document, video, conference)
     * @param string $disk The storage disk
     * @return array{path: string, url: string, filename: string, size: int, mime_type: string}
     * @throws \InvalidArgumentException
     */
    public function upload(UploadedFile $file, string $directory, string $context = 'image', string $disk = 'public'): array
    {
        $this->validateFile($file, $context);

        $filename = $this->generateFilename($file);
        $path = $file->storeAs($directory, $filename, $disk);

        return [
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Upload multiple files.
     *
     * @param array<UploadedFile> $files
     * @param string $directory
     * @param string $context
     * @param string $disk
     * @return array
     */
    public function uploadMultiple(array $files, string $directory, string $context = 'image', string $disk = 'public'): array
    {
        return collect($files)->map(fn ($file) => $this->upload($file, $directory, $context, $disk))->toArray();
    }

    /**
     * Delete a file from storage.
     */
    public function delete(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->delete($path);
    }

    /**
     * Validate the uploaded file.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateFile(UploadedFile $file, string $context): void
    {
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        if (!isset($this->allowedMimeTypes[$context])) {
            throw new \InvalidArgumentException("Unknown upload context: {$context}");
        }

        if (!in_array($mimeType, $this->allowedMimeTypes[$context])) {
            $allowed = implode(', ', $this->allowedMimeTypes[$context]);
            throw new \InvalidArgumentException("File type not allowed. Allowed types: {$allowed}");
        }

        $maxSize = $this->maxFileSizes[$context] ?? 10240;
        if ($fileSize > $maxSize * 1024) {
            throw new \InvalidArgumentException("File size exceeds maximum allowed size of {$maxSize}KB");
        }
    }

    /**
     * Generate a unique filename for the uploaded file.
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($basename);

        return sprintf('%s-%s.%s', $slug, Str::random(8), $extension);
    }

    /**
     * Get the allowed MIME types for a context.
     */
    public function getAllowedMimeTypes(string $context): array
    {
        return $this->allowedMimeTypes[$context] ?? [];
    }

    /**
     * Get the maximum file size for a context.
     */
    public function getMaxFileSize(string $context): int
    {
        return $this->maxFileSizes[$context] ?? 10240;
    }
}
