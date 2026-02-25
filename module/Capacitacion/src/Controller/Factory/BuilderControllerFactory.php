<?php
declare(strict_types=1);

namespace Capacitacion\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Capacitacion\Controller\BuilderController;

final class BuilderControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new BuilderController(
            $container->get(\Capacitacion\Model\Table\CapacitacionTable::class),
            $container->get(\Capacitacion\Model\Table\QuizTable::class),
            $container->get(\Capacitacion\Model\Table\QuestionTable::class),
            $container->get(\Capacitacion\Model\Table\AnswerTable::class),
            $container->get(\Capacitacion\Form\CapacitacionForm::class),
            $container->get(\Capacitacion\Form\QuizForm::class),
            $container->get(\Capacitacion\Form\QuestionForm::class),
            $container->get(\Capacitacion\Service\RewardRules::class)
        );
    }
}
