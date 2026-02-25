<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Db\Sql\Select;
use RuntimeException;

class FilesPointsContentTable
{

    private $tableGateway;
    private $storeTransactionsTable;
    public function __construct(TableGatewayInterface $tableGateway, StoreTransactionsTable $storeTransactionsTable)
    {
        $this->tableGateway = $tableGateway;
        $this->storeTransactionsTable = $storeTransactionsTable;
    }

    public function fetchAll()
    {
        $select = new Select;
        $select->from($this->tableGateway->getTable());
        $select->order('id DESC');
        return $this->tableGateway->selectWith($select);
    }

    public function add($data)
    {
        $this->tableGateway->insert($data);
        return $this->tableGateway->lastInsertValue;
    }

    public function getFile($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        return $rowset->current();
    }

    public function find($userId, $fileId)
    {
        $rowset = $this->tableGateway->select(['user_id' => $userId, 'file_id' => $fileId]);
        $resultSet = $rowset->buffer();
        $total = 0;
        foreach ($resultSet as $row) {
            $total = (int) $row->new_account_balance > $total ? (int) $row->new_account_balance : $total;
        }
        return $total;
    }

    public function changeFileStatus($fileId, $newStatus)
    {
        $data = [
            'estatus' => $newStatus
        ];
        $this->tableGateway->update($data, ['file_id' => $fileId]);
        if ($newStatus === 'Asignando') {
            $rowset = $this->tableGateway->select(function (Select $select) use ($fileId) {
                $select->where->greaterThan('user_id', 0);
                $select->where->equalTo('file_id', (int) $fileId);
            });
            $resultSet = $rowset->buffer();
            foreach ($resultSet as $row) {
                $data = [
                    'user_id' => $row->user_id,
                    'points' => $row->new_points,
                    'movement' => 'Add',
                    'concept' => $row->concept,
                    'details' => $fileId,
                    'month' => $this->getMonth($row->month)
                ];
                $this->storeTransactionsTable->add($data);
            }
        }
    }

    private function getMonth($month)
    {
        switch (strtoupper(trim($month))) {
            case 'ENERO':
                return 1;
            case 'FEBRERO':
                return 2;
            case 'MARZO':
                return 3;
            case 'ABRIL':
                return 4;
            case 'MAYO':
                return 5;
            case 'JUNIO':
                return 6;
            case 'JULIO':
                return 7;
            case 'AGOSTO':
                return 8;
            case 'SEPTIEMBRE':
                return 9;
            case 'OCTUBRE':
                return 10;
            case 'NOVIMEBRE':
                return 11;
            case 'DICIEMBRE':
                return 12;
        }
        return 0;
    }
}