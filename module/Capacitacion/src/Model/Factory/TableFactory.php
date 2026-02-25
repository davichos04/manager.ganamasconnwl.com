<?php
declare(strict_types=1);

namespace Capacitacion\Model\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class TableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $adapter = $container->get(AdapterInterface::class);
        $cfg = $container->get('config');
        $prefix = $cfg['capacitacion']['table_prefix'] ?? '';

        $map = [
            \Capacitacion\Model\Table\CapacitacionTable::class => $prefix . 'capacitaciones',
            \Capacitacion\Model\Table\QuizTable::class         => $prefix . 'capacitaciones_quizzes',
            \Capacitacion\Model\Table\QuestionTable::class     => $prefix . 'capacitaciones_questions',
            \Capacitacion\Model\Table\AnswerTable::class       => $prefix . 'capacitaciones_answers',
            \Capacitacion\Model\Table\ProductTable::class      => $prefix . 'store_products',
        ];

        if (!isset($map[$requestedName])) {
            throw new \RuntimeException('No table mapping for ' . $requestedName);
        }

        $gw = new TableGateway($map[$requestedName], $adapter);
        return new $requestedName($gw);
    }
}
