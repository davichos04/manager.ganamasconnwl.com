<?php

namespace Application\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Db\Sql\Select;
use RuntimeException;

class FilesTable
{

    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $select = new Select;
        $select->from($this->tableGateway->getTable());
        $select->order('id DESC');
        return $this->tableGateway->selectWith($select);
    }

    public function createFile($data, $userId, $fileType)
    {
        $data = [
            'filename' => $data['csv-file']['name'],
            'real_filename' => $data['csv-file']['tmp_name'],
            'estatus' => 'Pendiente',
            'uploaded_by' => $userId,
            'file_type' => $fileType
        ];
        $this->tableGateway->insert($data);
        return $this->tableGateway->lastInsertValue;
    }

    public function getFile($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        return $rowset->current();
    }

    public function changeFileStatus($fileId, $newStatus, $userId)
    {
        $data = [
            'estatus' => $newStatus,
            'modified_by' => $userId,
            'confirmed_at' => date("Y-m-d H:i:s", time())
        ];
        $this->tableGateway->update($data, ['id' => $fileId]);
    }
}