<?php
declare(strict_types=1);

namespace Capacitacion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Capacitacion\Model\Table\CapacitacionTable;
use Capacitacion\Model\Table\QuizTable;
use Capacitacion\Model\Table\QuestionTable;
use Capacitacion\Model\Table\AnswerTable;
use Capacitacion\Form\CapacitacionForm;
use Capacitacion\Form\QuizForm;
use Capacitacion\Form\QuestionForm;
use Capacitacion\Service\RewardRules;

final class BuilderController extends AbstractActionController
{
    public function __construct(
        private CapacitacionTable $capTable,
        private QuizTable $quizTable,
        private QuestionTable $questionTable,
        private AnswerTable $answerTable,
        private CapacitacionForm $capForm,
        private QuizForm $quizForm,
        private QuestionForm $questionForm,
        private RewardRules $rewardRules
    ) {}

    public function indexAction(): ViewModel
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id <= 0) {
            return $this->redirect()->toRoute('capacitacion-admin/cap', ['action'=>'index']);
        }

        $cap = $this->capTable->get($id);
        $quiz = $this->quizTable->getByCapacitacionId($id);

        if (!$quiz) {
            $quizId = $this->quizTable->save([
                'capacitacion_id' => $id,
                'title' => ($cap['title'] ?? 'Capacitacion') . ' | QUIZZ',
                'max_attempts' => 3,
                'pass_score' => 70,
                'published' => 1,
                'reward_mode' => 'none',
                'reward_product_id' => null,
                'reward_points' => null,
                'reward_limit' => 0,
                'reward_awarded_count' => 0,
            ]);
            $quiz = $this->quizTable->get($quizId);
        }

        $this->capForm->setData($cap);
        $this->quizForm->setData($quiz);

        $questions = $this->questionTable->fetchByQuizId((int)$quiz['id']);
        $answers = [];
        foreach ($questions as $q) {
            $answers[(int)$q['id']] = $this->answerTable->fetchByQuestionId((int)$q['id']);
        }

        $this->questionForm->get('quiz_id')->setValue((string)$quiz['id']);

        return new ViewModel([
            'cap' => $cap,
            'quiz' => $quiz,
            'capForm' => $this->capForm,
            'quizForm' => $this->quizForm,
            'questionForm' => $this->questionForm,
            'questions' => $questions,
            'answers' => $answers,
        ]);
    }
}
