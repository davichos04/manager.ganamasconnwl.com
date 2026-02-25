<?php
declare(strict_types=1);

namespace Capacitacion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Capacitacion\Model\Table\QuizTable;
use Capacitacion\Form\QuizForm;
use Capacitacion\Service\RewardRules;

final class QuizController extends AbstractActionController
{
    public function __construct(
        private QuizTable $quizTable,
        private QuizForm $quizForm,
        private RewardRules $rewardRules
    ) {}

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id <= 0) return $this->redirect()->toRoute('capacitacion-admin/cap', ['action'=>'index']);

        $quiz = $this->quizTable->get($id);
        $form = $this->quizForm;
        $form->setData($quiz);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data['id'] = $id;
            $data['published'] = isset($data['published']) ? 1 : 0;

            $form->setData(array_merge($quiz, $data));
            if ($form->isValid()) {
                $clean = $this->rewardRules->normalizeAndValidate($form->getData());
                $this->quizTable->save($clean, $id);
                return $this->redirect()->toRoute('capacitacion-admin/builder', ['id' => (int)$quiz['capacitacion_id']]);
            }
        }

        return new ViewModel(['form' => $form, 'quiz' => $quiz]);
    }
}
