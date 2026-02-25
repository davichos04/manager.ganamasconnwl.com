<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\InputFilter;

use RuntimeException;

class OrdersTable
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

    public function fetchByUserId($userId)
    {
        $rowset = $this->tableGateway->select(['user_id' => $userId]);
        return $rowset->buffer();
    }
    public function update($data, $id)
    {
        return $this->tableGateway->update($data, ['id' => $id]);
    }
}