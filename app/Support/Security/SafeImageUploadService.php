<?php

declare(strict_types=1);

namespace App\Support\Security;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class SafeImageUploadService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const MAX_FILE_SIZE_BYTES = 4194304; // 4 MB

    private const MAX_WIDTH = 800;
    private const MAX_HEIGHT = 600;
    private const WEBP_QUALITY = 80;

    /**
     * Inspects magic bytes, strips all EXIF/GPS metadata, resizes and stores as WebP under a tenant-isolated path.
     *
     * @param UploadedFile $file
     * @param int $lojaId
     * @return array{path: string, url: string, width: int, height: int, size_bytes: int}
     */
    public function uploadProductImage(UploadedFile $file, int $lojaId): array
    {
        if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            throw new InvalidArgumentException('Arquivo excede o tamanho máximo permitido de 4 MB.');
        }

        $tempPath = $file->getRealPath();
        if ($tempPath === false || !file_exists($tempPath)) {
            throw new RuntimeException('Arquivo temporário inválido ou inacessível.');
        }

        if (isset($file->tempFile) && is_resource($file->tempFile)) {
            rewind($file->tempFile);
            $rawContent = stream_get_contents($file->tempFile);
        } else {
            $rawContent = @file_get_contents($tempPath);
        }
        if (!is_string($rawContent) || $rawContent === '') {
            throw new RuntimeException('Falha ao ler o conteúdo do arquivo.');
        }

        // Magic-byte inspection on the buffer (portable: avoids locked temp-file handles on Windows)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = $finfo !== false ? finfo_buffer($finfo, $rawContent) : false;
        if ($finfo !== false) {
            finfo_close($finfo);
        }

        if (!is_string($realMime) || !in_array($realMime, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException(
                "Tipo de arquivo não permitido ({$realMime}). Apenas JPEG, PNG e WebP são aceitos."
            );
        }

        $sourceImage = @imagecreatefromstring($rawContent);
        if ($sourceImage === false) {
            throw new InvalidArgumentException('Arquivo de imagem corrompido ou payload inválido.');
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        [$targetWidth, $targetHeight] = $this->calculateTargetDimensions($origWidth, $origHeight);

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($targetImage === false) {
            imagedestroy($sourceImage);
            throw new RuntimeException('Falha ao instanciar canvas gráfico para redimensionamento.');
        }

        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        if ($transparent !== false) {
            imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $origWidth,
            $origHeight
        );

        ob_start();
        $webpSuccess = imagewebp($targetImage, null, self::WEBP_QUALITY);
        $webpData = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        if (!$webpSuccess || !is_string($webpData)) {
            throw new RuntimeException('Falha na conversão da imagem para o formato WebP.');
        }

        $uuid = (string) Str::uuid();
        $storagePath = "lojas/{$lojaId}/produtos/{$uuid}.webp";

        Storage::disk('public')->put($storagePath, $webpData);

        return [
            'path' => $storagePath,
            'url' => Storage::disk('public')->url($storagePath),
            'width' => $targetWidth,
            'height' => $targetHeight,
            'size_bytes' => strlen($webpData),
        ];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function calculateTargetDimensions(int $width, int $height): array
    {
        if ($width <= self::MAX_WIDTH && $height <= self::MAX_HEIGHT) {
            return [$width, $height];
        }

        $ratio = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height);

        return [
            (int) max(1, round($width * $ratio)),
            (int) max(1, round($height * $ratio)),
        ];
    }
}
