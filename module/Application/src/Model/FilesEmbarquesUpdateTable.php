<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Db\Sql\Select;
use RuntimeException;

class FilesEmbarquesUpdateTable
{

    private $tableGateway;
    private $ordersTable;

    public function __construct(TableGatewayInterface $tableGateway, OrdersTable $ordersTable)
    {
        $this->tableGateway = $tableGateway;
        $this->ordersTable = $ordersTable;
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
        if ($newStatus === 'Procesado') {
            $rowset = $this->tableGateway->select(function (Select $select) use ($fileId) {
                $select->where->greaterThan('user_id', 0);
                $select->where->equalTo('file_id', (int) $fileId);
            });
            $resultSet = $rowset->buffer();
            foreach ($resultSet as $row) {
                $data = [
                    'tracking_status' => $this->getEstatus($row->new_status),
                    'tracking_received' => $this->getDate($row->release_at),
                    'carrier' => $row->carrier,
                    'pickup_date' => $this->getDate($row->picked_at),
                    'real_date_delivery' => $this->getDate($row->release_at),
                    'person_who_recibe' => $row->who_recibes,
                    'guide_snumber' => $row->guide,

                ];
                $this->ordersTable->update($data, trim($row->order_detail_id));
            }
        }
    }

    private function getEstatus($estatus)
    {
        if (stripos(strtolower($estatus), 'envi') !== false) {
            return 2;
        }
        if (stripos(strtolower($estatus), 'entreg') !== false) {
            return 3;
        }
        return 1;
    }

    private function getDate($date)
    {


        if (strlen($date) < 5) {
            return '';
        }
        if ((strpos($date, '/') === false) && (strpos($date, '-') === false)) {
            $unixtime = ($date - 25569) * 86400;
            return date('d/m/Y', $unixtime);
        }
        return str_replace('-', '/', $date);
    }
}