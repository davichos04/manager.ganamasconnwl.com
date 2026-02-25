<?php
declare(strict_types=1);

namespace Capacitacion\Service;

use Laminas\Validator\File\Size as SizeValidator;
use Laminas\Validator\File\MimeType as MimeTypeValidator;

final class FileUploadService
{
    public function __construct(
        private string $absImagesBaseDir,
        private string $urlImagesBase,
        private string $moduleDir,
        private int $maxBytes,
        private array $allowedMime
    ) {}

    /** @return array{rel:string, abs:string, url:string} */
    private function buildPaths(int $capId, string $filename): array
    {
        $rel = trim($this->urlImagesBase, '/') . '/' . trim($this->moduleDir, '/') . '/' . $capId . '/' . $filename;
        $abs = rtrim($this->absImagesBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($this->moduleDir, '/')
            . DIRECTORY_SEPARATOR . $capId . DIRECTORY_SEPARATOR . $filename;
        $url = '/' . $rel;
        return ['rel' => $rel, 'abs' => $abs, 'url' => $url];
    }

    private function ensureDir(int $capId): string
    {
        $dir = rtrim($this->absImagesBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($this->moduleDir, '/')
            . DIRECTORY_SEPARATOR . $capId;

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException('No se pudo crear el directorio: ' . $dir);
            }
        }
        return $dir;
    }

    /** @return string|null Rel path to store in DB, e.g. images/capacitacion/12/abc.jpg */
    public function uploadQuestionImage(array $file, int $capId, ?string $oldRelPath = null): ?string
    {
        if (empty($file) || empty($file['tmp_name']) || (int)($file['error'] ?? 0) !== 0) {
            return null;
        }

        $size = new SizeValidator(['max' => $this->maxBytes]);
        $mime = new MimeTypeValidator($this->allowedMime);

        if (!$size->isValid($file) || !$mime->isValid($file)) {
            $msgs = array_merge($size->getMessages(), $mime->getMessages());
            throw new \DomainException('Archivo inválido: ' . implode(' | ', $msgs));
        }

        $this->ensureDir($capId);

        $ext = pathinfo($file['name'] ?? 'img', PATHINFO_EXTENSION);
        $ext = $ext ? strtolower($ext) : 'jpg';
        $ext = preg_replace('/[^a-z0-9]+/i', '', $ext) ?: 'jpg';

        $safe = bin2hex(random_bytes(12)) . '.' . $ext;

        $paths = $this->buildPaths($capId, $safe);
        $absTarget = $paths['abs'];

        if (!move_uploaded_file($file['tmp_name'], $absTarget)) {
            throw new \RuntimeException('No se pudo mover el archivo subido');
        }

        // delete old file if provided and is inside images/capacitacion/{capId}/
        if ($oldRelPath) {
            $oldAbs = rtrim($this->absImagesBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $oldRelPath), DIRECTORY_SEPARATOR);
            if (is_file($oldAbs)) {
                @unlink($oldAbs);
            }
        }

        return $paths['rel'];
    }

    public function deleteByRel(?string $relPath): void
    {
        if (!$relPath) return;
        $abs = rtrim($this->absImagesBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $relPath), DIRECTORY_SEPARATOR);
        if (is_file($abs)) {
            @unlink($abs);
        }
    }


    public function storeCapMedia(int $capId, array $file): string
    {
        return $this->storeCapFileGeneric($capId, $file, 'media');
    }

    public function storeCapThumb(int $capId, array $file): string
    {
        return $this->storeCapFileGeneric($capId, $file, 'thumb');
    }

    private function storeCapFileGeneric(int $capId, array $file, string $prefix): string
    {
        $name = $file['name'] ?? '';
        if ($name === '') {
            throw new \RuntimeException('Archivo inválido');
        }

        $size = (int)($file['size'] ?? 0);
        if ($size > (2 * 1024 * 1024)) {
            throw new \RuntimeException('Máximo 2MB');
        }

        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('Upload inválido');
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp','pdf'];
        if (!in_array($ext, $allowed, true)) {
            throw new \RuntimeException('Tipo de archivo no permitido');
        }

        $dir = rtrim($this->baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . $this->moduleDir . DIRECTORY_SEPARATOR . $capId;

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException('No se pudo crear directorio de upload');
            }
        }

        $filename = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('No se pudo mover el archivo');
        }

        // relative path stored in DB, usable as /images/...
        return trim($this->imagesUrlBase, '/') . '/' . $this->moduleDir . '/' . $capId . '/' . $filename;
    }

}
