<?php

declare(strict_types=1);

namespace Application\Model;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;

class CapacitacionesTable
{
    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchTree(): array
    {
        $sql = new Sql($this->adapter);

        $caps = [];
        $capSelect = new Select('j6bp1_capacitaciones');
        $capSelect->order('id DESC');
        $capResult = $sql->prepareStatementForSqlObject($capSelect)->execute();
        foreach ($capResult as $row) {
            $row = (array) $row;
            $row['quizzes'] = [];
            $caps[$row['id']] = $row;
        }

        if (empty($caps)) {
            return [];
        }

        $quizSelect = new Select('j6bp1_capacitaciones_quizzes');
        $quizSelect->where(['capacitacion_id' => array_keys($caps)]);
        $quizSelect->order('id ASC');
        $quizResult = $sql->prepareStatementForSqlObject($quizSelect)->execute();

        $quizzes = [];
        foreach ($quizResult as $quiz) {
            $quiz = (array) $quiz;
            $quiz['questions'] = [];
            $quizzes[$quiz['id']] = $quiz;
            if (isset($caps[$quiz['capacitacion_id']])) {
                $caps[$quiz['capacitacion_id']]['quizzes'][] = &$quizzes[$quiz['id']];
            }
        }

        if (!empty($quizzes)) {
            $questionSelect = new Select('j6bp1_capacitaciones_questions');
            $questionSelect->where(['quiz_id' => array_keys($quizzes)]);
            $questionSelect->order('ordering ASC');
            $questionResult = $sql->prepareStatementForSqlObject($questionSelect)->execute();

            $questions = [];
            foreach ($questionResult as $question) {
                $question = (array) $question;
                $question['answers'] = [];
                $questions[$question['id']] = $question;
                if (isset($quizzes[$question['quiz_id']])) {
                    $quizzes[$question['quiz_id']]['questions'][] = &$questions[$question['id']];
                }
            }

            if (!empty($questions)) {
                $answerSelect = new Select('j6bp1_capacitaciones_answers');
                $answerSelect->where(['question_id' => array_keys($questions)]);
                $answerSelect->order('id ASC');
                $answerResult = $sql->prepareStatementForSqlObject($answerSelect)->execute();
                foreach ($answerResult as $answer) {
                    $answer = (array) $answer;
                    if (isset($questions[$answer['question_id']])) {
                        $questions[$answer['question_id']]['answers'][] = $answer;
                    }
                }
            }
        }

        return array_values($caps);
    }

    public function fetchRewardProducts(): array
    {
        $sql = new Sql($this->adapter);
        $select = new Select('j6bp1_store_products');
        $select->columns(['id', 'sku', 'title']);
        $select->where(['type' => 'ereward']);
        $select->order('sku ASC');

        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $rows = [];
        foreach ($result as $row) {
            $row = (array) $row;
            $rows[] = [
                'id' => (int) $row['id'],
                'label' => $row['sku'] . ' - ' . $row['title'],
            ];
        }
        return $rows;
    }

    public function saveCapacitacion(array $data, ?int $id = null): int
    {
        $sql = new Sql($this->adapter);
        $now = date('Y-m-d H:i:s');

        if ($id) {
            $data['modified'] = $now;
            $update = new Update('j6bp1_capacitaciones');
            $update->set($data);
            $update->where(['id' => $id]);
            $sql->prepareStatementForSqlObject($update)->execute();
            return $id;
        }

        $data['created'] = $now;
        $insert = new Insert('j6bp1_capacitaciones');
        $insert->values($data);
        $result = $sql->prepareStatementForSqlObject($insert)->execute();
        return (int) $result->getGeneratedValue();
    }

    public function saveQuiz(array $data, ?int $id = null): int
    {
        $sql = new Sql($this->adapter);
        if ($id) {
            $update = new Update('j6bp1_capacitaciones_quizzes');
            $update->set($data);
            $update->where(['id' => $id]);
            $sql->prepareStatementForSqlObject($update)->execute();
            return $id;
        }

        $insert = new Insert('j6bp1_capacitaciones_quizzes');
        $insert->values($data);
        $result = $sql->prepareStatementForSqlObject($insert)->execute();
        return (int) $result->getGeneratedValue();
    }

    public function saveQuestion(array $data, ?int $id = null): int
    {
        $sql = new Sql($this->adapter);
        if ($id) {
            $update = new Update('j6bp1_capacitaciones_questions');
            $update->set($data);
            $update->where(['id' => $id]);
            $sql->prepareStatementForSqlObject($update)->execute();
            return $id;
        }

        $insert = new Insert('j6bp1_capacitaciones_questions');
        $insert->values($data);
        $result = $sql->prepareStatementForSqlObject($insert)->execute();
        return (int) $result->getGeneratedValue();
    }

    public function saveAnswer(array $data, ?int $id = null): int
    {
        $sql = new Sql($this->adapter);
        if ($id) {
            $update = new Update('j6bp1_capacitaciones_answers');
            $update->set($data);
            $update->where(['id' => $id]);
            $sql->prepareStatementForSqlObject($update)->execute();
            return $id;
        }

        $insert = new Insert('j6bp1_capacitaciones_answers');
        $insert->values($data);
        $result = $sql->prepareStatementForSqlObject($insert)->execute();
        return (int) $result->getGeneratedValue();
    }

    public function deleteCapacitacion(int $id): void
    {
        $sql = new Sql($this->adapter);

        $quizIds = [];
        $quizSelect = new Select('j6bp1_capacitaciones_quizzes');
        $quizSelect->columns(['id']);
        $quizSelect->where(['capacitacion_id' => $id]);
        $quizResult = $sql->prepareStatementForSqlObject($quizSelect)->execute();
        foreach ($quizResult as $row) {
            $quizIds[] = (int) $row['id'];
        }

        if (!empty($quizIds)) {
            $questionIds = [];
            $questionSelect = new Select('j6bp1_capacitaciones_questions');
            $questionSelect->columns(['id']);
            $questionSelect->where(['quiz_id' => $quizIds]);
            $questionResult = $sql->prepareStatementForSqlObject($questionSelect)->execute();
            foreach ($questionResult as $row) {
                $questionIds[] = (int) $row['id'];
            }

            if (!empty($questionIds)) {
                $answerDelete = new Delete('j6bp1_capacitaciones_answers');
                $answerDelete->where(['question_id' => $questionIds]);
                $sql->prepareStatementForSqlObject($answerDelete)->execute();

                $questionDelete = new Delete('j6bp1_capacitaciones_questions');
                $questionDelete->where(['id' => $questionIds]);
                $sql->prepareStatementForSqlObject($questionDelete)->execute();
            }

            $quizDelete = new Delete('j6bp1_capacitaciones_quizzes');
            $quizDelete->where(['id' => $quizIds]);
            $sql->prepareStatementForSqlObject($quizDelete)->execute();
        }

        $capDelete = new Delete('j6bp1_capacitaciones');
        $capDelete->where(['id' => $id]);
        $sql->prepareStatementForSqlObject($capDelete)->execute();
    }

    public function deleteQuiz(int $id): void
    {
        $sql = new Sql($this->adapter);
        $questionIds = [];
        $questionSelect = new Select('j6bp1_capacitaciones_questions');
        $questionSelect->columns(['id']);
        $questionSelect->where(['quiz_id' => $id]);
        $questionResult = $sql->prepareStatementForSqlObject($questionSelect)->execute();
        foreach ($questionResult as $row) {
            $questionIds[] = (int) $row['id'];
        }

        if (!empty($questionIds)) {
            $answerDelete = new Delete('j6bp1_capacitaciones_answers');
            $answerDelete->where(['question_id' => $questionIds]);
            $sql->prepareStatementForSqlObject($answerDelete)->execute();

            $questionDelete = new Delete('j6bp1_capacitaciones_questions');
            $questionDelete->where(['id' => $questionIds]);
            $sql->prepareStatementForSqlObject($questionDelete)->execute();
        }

        $quizDelete = new Delete('j6bp1_capacitaciones_quizzes');
        $quizDelete->where(['id' => $id]);
        $sql->prepareStatementForSqlObject($quizDelete)->execute();
    }

    public function deleteQuestion(int $id): void
    {
        $sql = new Sql($this->adapter);
        $answerDelete = new Delete('j6bp1_capacitaciones_answers');
        $answerDelete->where(['question_id' => $id]);
        $sql->prepareStatementForSqlObject($answerDelete)->execute();

        $questionDelete = new Delete('j6bp1_capacitaciones_questions');
        $questionDelete->where(['id' => $id]);
        $sql->prepareStatementForSqlObject($questionDelete)->execute();
    }

    public function deleteAnswer(int $id): void
    {
        $sql = new Sql($this->adapter);
        $delete = new Delete('j6bp1_capacitaciones_answers');
        $delete->where(['id' => $id]);
        $sql->prepareStatementForSqlObject($delete)->execute();
    }
}
