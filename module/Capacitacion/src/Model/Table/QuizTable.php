<?php
declare(strict_types=1);

namespace Capacitacion\Model\Table;

use Laminas\Db\TableGateway\TableGatewayInterface;

final class QuizTable
{
    public function __construct(private TableGatewayInterface $gw) {}

    public function getByCapacitacionId(int $capId): ?array
    {
        $row = $this->gw->select(['capacitacion_id' => $capId])->current();
        return $row ? (array)$row : null;
    }

    public function get(int $id): array
    {
        $row = $this->gw->select(['id' => $id])->current();
        if (!$row) throw new \RuntimeException('Quiz no encontrado: ' . $id);
        return (array)$row;
    }

    public function save(array $data, ?int $id = null): int
    {
        if ($id) {
            $this->gw->update($data, ['id' => $id]);
            return $id;
        }
        $this->gw->insert($data);
        return (int)$this->gw->getLastInsertValue();
    }
}
