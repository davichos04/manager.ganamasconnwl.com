<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;

use RuntimeException;

class ReportOrderHistoryTable
{

    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    public function fetchAll()
    {
        $rowset = $this->tableGateway->select();
        return $rowset->buffer();
    }

}