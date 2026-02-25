<?php
declare(strict_types=1);

namespace Capacitacion\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Capacitacion\Controller\CapacitacionController;

final class CapacitacionControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new CapacitacionController(
            $container->get(\Capacitacion\Model\Table\CapacitacionTable::class),
            $container->get(\Capacitacion\Form\CapacitacionForm::class)
        );
    }
}
