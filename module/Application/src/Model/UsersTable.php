<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\InputFilter;

use RuntimeException;

class UsersTable
{

    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAccountByUsername($username)
    {
        $rowset = $this->tableGateway->select(['username' => $username]);
        $row = $rowset->current();
        if (!$row) {
            return null;
        }
        return $row;

    }
    


}