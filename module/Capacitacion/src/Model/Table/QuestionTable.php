<?php
declare(strict_types=1);

namespace Capacitacion\Model\Table;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Db\Sql\Select;

final class QuestionTable
{
    public function __construct(private TableGatewayInterface $gw) {}

    public function fetchByQuizId(int $quizId): array
    {
        $rs = $this->gw->select(function (Select $s) use ($quizId) {
            $s->where(['quiz_id' => $quizId])->order('ordering ASC, id ASC');
        });
        return array_map(fn($r) => (array)$r, iterator_to_array($rs));
    }

    public function get(int $id): array
    {
        $row = $this->gw->select(['id' => $id])->current();
        if (!$row) throw new \RuntimeException('Pregunta no encontrada: ' . $id);
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

    public function delete(int $id): void
    {
        $this->gw->delete(['id' => $id]);
    }
}
