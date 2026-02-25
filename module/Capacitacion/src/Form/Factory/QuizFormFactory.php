<?php
declare(strict_types=1);

namespace Capacitacion\Form\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Capacitacion\Form\QuizForm;
use Capacitacion\Model\Table\ProductTable;

final class QuizFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $form = new QuizForm();
        $opts = $container->get(ProductTable::class)->fetchERewardsOptions();
        $form->get('reward_product_id')->setValueOptions($opts);
        return $form;
    }
}
