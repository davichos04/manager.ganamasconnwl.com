<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Db\Sql\Select;
use RuntimeException;

class JUsersTable {

    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll() {
        $rowset = $this->tableGateway->select();
        return $rowset->buffer();
    }

    public function fetchById($id) {
        $rowset = $this->tableGateway->select(['id' => $id]);
        return $rowset->current();
    }

    public function fetchByUsername($username) {
        $rowset = $this->tableGateway->select(['username' => $username]);
        $row = $rowset->current();
        if (!$row) {
            return null;
        }
        return $row;
    }

    public function getStats() {
        $rowset = $this->tableGateway->select();
        $resultSet = $rowset->buffer();
        $total = $resultSet->count();
        $loggedIn = $completed = $pending = $active = $deleted = 0;
        foreach ($resultSet as $row) {
            $loggedIn += ($row->lastvisitDate !== '2000-01-01 00:00:00' && $row->lastvisitDate !== '0000-00-00 00:00:00') ? 1 : 0;
            $completed += ($row->data_completed_at !== NULL) ? 1 : 0;
            if ($row->estatus == 'Activo') {
                $active++;
            } else {
                $deleted++;
            }
            
        }
        return [
            'total' => $total,
            'active' => $active,
            'deleted' => $deleted,
            'logged-in' => $loggedIn,
            'completed' => $completed,
            'pending' => $total - $completed
        ];
    }

}
