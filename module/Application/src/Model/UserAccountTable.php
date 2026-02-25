<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\InputFilter;

use RuntimeException;

class UserAccountTable
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
        $rowset = $this->tableGateway->select(['id' => $userId]);
        return $rowset->current();
    }
    public function fetchByUsername($username)
    {
        $rowset = $this->tableGateway->select(['username' => $username]);
        return $rowset->current();
    }
}