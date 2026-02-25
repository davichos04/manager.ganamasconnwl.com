<?php
declare(strict_types=1);

namespace Capacitacion\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Capacitacion\Controller\QuizController;

final class QuizControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new QuizController(
            $container->get(\Capacitacion\Model\Table\QuizTable::class),
            $container->get(\Capacitacion\Form\QuizForm::class),
            $container->get(\Capacitacion\Service\RewardRules::class)
        );
    }
}
