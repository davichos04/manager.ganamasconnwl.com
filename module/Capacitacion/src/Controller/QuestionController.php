<?php

declare(strict_types=1);

namespace Capacitacion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\View\Renderer\PhpRenderer;
use Capacitacion\Model\Table\QuestionTable;
use Capacitacion\Model\Table\QuizTable;
use Capacitacion\Form\QuestionForm;
use Capacitacion\Service\FileUploadService;

final class QuestionController extends AbstractActionController
{
    public function __construct(
        private QuestionTable $questionTable,
        private QuizTable $quizTable,
        private QuestionForm $questionForm,
        private FileUploadService $uploader,
        private PhpRenderer $renderer,
        private \Capacitacion\Model\Table\AnswerTable $answerTable
    ) {}

    public function addAction()
    {
        $quizId = (int)$this->params()->fromQuery('quiz_id', 0);
        if ($quizId <= 0) return $this->redirect()->toRoute('capacitacion-admin/cap', ['action' => 'index']);

        $quiz = $this->quizTable->get($quizId);
        $capId = (int)$quiz['capacitacion_id'];

        $form = $this->questionForm;
        $form->get('quiz_id')->setValue((string)$quizId);
        $form->setAttribute('action', $this->url()->fromRoute('capacitacion-admin/question', ['action' => 'add']));

        $isAjax = $this->getRequest()->isXmlHttpRequest() || ((string)$this->params()->fromQuery('ajax', '') === '1');
        if ($isAjax && !$this->getRequest()->isPost()) {
            $vm = new ViewModel(['title' => 'Nueva pregunta', 'form' => $form, 'submitText' => 'Guardar', 'quiz' => $quiz]);
            $vm->setTemplate('capacitacion/partial/modal-form');
            $vm->setTerminal(true);
            return $vm;
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $files = $this->params()->fromFiles();

            $post['quiz_id'] = $quizId;
            $post['published'] = isset($post['published']) ? 1 : 0;
            $post['ordering'] = isset($post['ordering']) ? (int)$post['ordering'] : 0;

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                unset($data['submit'], $data['csrf']);
                
                // preserve existing image if no new file
                $files = $this->getRequest()->getFiles()->toArray();
                if (empty($files['image_file']['name'])) {
                    $data['image'] = $row['image'] ?? null;
                }

                $imgRel = $this->uploader->uploadQuestionImage($files['image'] ?? [], $capId, null);
                if ($imgRel) $data['image'] = $imgRel;

                $newId = $this->questionTable->save($data);
                /*if ($isAjax) {
                    $q = $this->questionTable->get((int)$newId);
                    $answers = [];
                    $answers[(int)$q['id']] = $this->answerTable->fetchByQuestionId((int)$q['id']);
                    $cardVm = new ViewModel(['q' => $q, 'capId' => $capId, 'answers' => $answers]);
                    $cardVm->setTemplate('capacitacion/partial/question-card');
                    $cardVm->setTerminal(true);
                    $cardHtml = $this->renderer->render($cardVm);
                    return new JsonModel(['success' => true, 'questionId' => (int)$q['id'], 'questionCardHtml' => $cardHtml]);
                }*/
                if ($isAjax) {
                    return new JsonModel(['success' => true, 'questionId' => $id]);
                }
                return $this->redirect()->toRoute('capacitacion-admin/builder', ['id' => $capId]);
            }
        }

        return new ViewModel(['form' => $form, 'quiz' => $quiz]);
    }

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id <= 0) return $this->redirect()->toRoute('capacitacion-admin/cap', ['action' => 'index']);

        $row = $this->questionTable->get($id);
        $quiz = $this->quizTable->get((int)$row['quiz_id']);
        $capId = (int)$quiz['capacitacion_id'];

        $form = $this->questionForm;
        $form->setData($row);
        $form->setAttribute('action', $this->url()->fromRoute('capacitacion-admin/question', ['action' => 'edit', 'id' => $id]));

        $isAjax = $this->getRequest()->isXmlHttpRequest() || ((string)$this->params()->fromQuery('ajax', '') === '1');
        if ($isAjax && !$this->getRequest()->isPost()) {
            $vm = new ViewModel(['title' => 'Editar pregunta', 'form' => $form, 'submitText' => 'Guardar cambios', 'row' => $row, 'quiz' => $quiz, 'currentImage' => ($row['image'] ?? null)]);
            $vm->setTemplate('capacitacion/partial/modal-form');
            $vm->setTerminal(true);
            return $vm;
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $files = $this->params()->fromFiles();

            $post['id'] = $id;
            $post['quiz_id'] = (int)$row['quiz_id'];
            $post['published'] = isset($post['published']) ? 1 : 0;
            $post['ordering'] = isset($post['ordering']) ? (int)$post['ordering'] : 0;

            $form->setData(array_merge($row, $post));
            if ($form->isValid()) {
                $data = $form->getData();
                unset($data['submit'], $data['csrf']);

                // preserve existing image if no new file
                $files = $this->getRequest()->getFiles()->toArray();
                if (empty($files['image_file']['name'])) {
                    $data['image'] = $row['image'] ?? null;
                }

                $imgRel = $this->uploader->uploadQuestionImage($files['image'] ?? [], $capId, (string)($row['image'] ?? null));

                if ($imgRel) $data['image'] = $imgRel;

                $this->questionTable->save($data, $id);

                /*if ($isAjax) {
                    $q = $this->questionTable->get($id);
                    $answers = [];
                    $answers[(int)$q['id']] = $this->answerTable->fetchByQuestionId((int)$q['id']);
                    $cardVm = new ViewModel(['q' => $q, 'capId' => $capId, 'answers' => $answers]);
                    $cardVm->setTemplate('capacitacion/partial/question-card');
                    $cardVm->setTerminal(true);
                    $cardHtml = $this->renderer->render($cardVm);
                    return new JsonModel(['success' => true, 'questionId' => (int)$q['id'], 'questionCardHtml' => $cardHtml]);
                }*/
                if ($isAjax) {
                    return new ViewModel(['data'=>['success' => true, 'questionId' => $id],'isAjax'=>$isAjax]);
                }
                return $this->redirect()->toRoute('capacitacion-admin/builder', ['id' => $capId]);
            }
        }

        return new ViewModel(['form' => $form, 'row' => $row, 'quiz' => $quiz]);
    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        $isAjax = $this->getRequest()->isXmlHttpRequest() || ((string)$this->params()->fromPost('ajax', '') === '1');
        if ($id > 0) {
            $row = $this->questionTable->get($id);
            $quiz = $this->quizTable->get((int)$row['quiz_id']);
            $capId = (int)$quiz['capacitacion_id'];

            $this->uploader->deleteByRel($row['image'] ?? null);
            $this->questionTable->delete($id);

            if ($isAjax) {
                return new JsonModel(['success' => true, 'questionId' => $id]);
            }
            return $this->redirect()->toRoute('capacitacion-admin/builder', ['id' => $capId]);
        }
        return $this->redirect()->toRoute('capacitacion-admin/cap', ['action' => 'index']);
    }
}
