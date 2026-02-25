<?php
declare(strict_types=1);

namespace Capacitacion\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Capacitacion\Service\FileUploadService;

final class FileUploadServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $cfg = $container->get('config');
        $c = $cfg['capacitacion'] ?? [];

        $abs = (string)($c['images_abs_base_dir'] ?? '');
        $url = (string)($c['images_url_base'] ?? '/images');
        $dir = (string)($c['images_module_dir'] ?? 'capacitacion');
        $max = (int)($c['max_upload_bytes'] ?? (2 * 1024 * 1024));
        $mime = $c['allowed_mime'] ?? ['image/jpeg','image/png','image/gif','image/webp'];

        if ($abs === '' || !is_dir($abs)) {
            throw new \RuntimeException('Config inválida: capacitacion.images_abs_base_dir debe apuntar al directorio real de /images');
        }

        return new FileUploadService($abs, $url, $dir, $max, $mime);
    }
}
