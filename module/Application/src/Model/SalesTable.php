<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Db\Sql\Select;

class SalesTable
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

    public function fetchAllPending()
    {
        $rowset = $this->tableGateway->select(function (Select $select) {
            $select->where->lessThan('tracking_status', 3);
        });
        return $rowset->buffer();
    }

    public function fetchByUserId($userId)
    {
        $rowset = $this->tableGateway->select(['order_id' => $userId]);
        return $rowset->current();
    }
    public function add($data)
    {
        $this->tableGateway->insert($data);
        return $this->tableGateway->lastInsertValue;
    }
    public function fetchByADN($adn)
    {
        $rowset = $this->tableGateway->select(function (Select $select) use ($adn) {
            $select->where->like('adn', trim($adn));
        });
        return $rowset->current();
    }
}