<?php
declare(strict_types=1);

namespace Capacitacion\Model\Table;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Db\Sql\Select;

final class ProductTable
{
    public function __construct(private TableGatewayInterface $gw) {}

    public function fetchERewardsOptions(): array
    {
        $rs = $this->gw->select(function (Select $s) {

            $s->where(['type' => 'ereward'])->order('title ASC');
        });

        $opts = ['' => '— Selecciona un e-reward —'];
        foreach ($rs as $row) {
            $r = (array)$row;
            $label = ($r['title'] ?? ('Producto #' . ($r['id'] ?? '')));
            $opts[(string)$r['id']] = $label;
        }
        return $opts;
    }
}
