<?php
declare(strict_types=1);

namespace Capacitacion\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Capacitacion\Controller\QuestionController;

final class QuestionControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new QuestionController(
            $container->get(\Capacitacion\Model\Table\QuestionTable::class),
            $container->get(\Capacitacion\Model\Table\QuizTable::class),
            $container->get(\Capacitacion\Form\QuestionForm::class),
            $container->get(\Capacitacion\Service\FileUploadService::class),
            $container->get('ViewRenderer'),
            $container->get(\Capacitacion\Model\Table\AnswerTable::class)
        );
    }
}
