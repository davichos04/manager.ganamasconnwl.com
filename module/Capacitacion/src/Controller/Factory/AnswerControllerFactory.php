<?php
declare(strict_types=1);

namespace Capacitacion\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Capacitacion\Controller\AnswerController;

final class AnswerControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new AnswerController(
            $container->get(\Capacitacion\Model\Table\AnswerTable::class),
            $container->get(\Capacitacion\Model\Table\QuestionTable::class),
            $container->get(\Capacitacion\Form\AnswerForm::class),
            $container->get('ViewRenderer')
        );
    }
}
