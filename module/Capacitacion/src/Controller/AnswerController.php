<?php

declare(strict_types=1);

namespace Capacitacion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\View\Renderer\PhpRenderer;
use Capacitacion\Model\Table\AnswerTable;
use Capacitacion\Model\Table\QuestionTable;
use Capacitacion\Form\AnswerForm;

final class AnswerController extends AbstractActionController
{
    public function __construct(
        private AnswerTable $answerTable,
        private QuestionTable $questionTable,
        private AnswerForm $answerForm,
        private PhpRenderer $renderer
    ) {}

    public function addAction()
    {
        $isAjax = $this->getRequest()->isXmlHttpRequest() || ((string)$this->params()->fromQuery('ajax', '') === '1');
        $isPost = $this->getRequest()->isPost();
        $post = $this->params()->fromPost();
        $questionId = (int)(isset($post['question_id']) ? $post['question_id'] : $this->params()->fromQuery('question_id', 0));
        $form = $this->answerForm;
        $form->get('question_id')->setValue((string)$questionId);
        $form->setAttribute('action', $this->url()->fromRoute('capacitacion-admin/answer', ['action' => 'add']));

        if ($isPost && $questionId <= 0) {
            if (!$isAjax) {
                return $this->redirect()->toRoute('capacitacion-admin/cap', ['action' => 'index']);
            } else {
                return new ViewModel(['isAjax' => $isAjax, 'data' => ['success' => false, 'msj' => 'Question needed', 'form' => $form, 'question' => 0]]);
            }
        }
        if ($isAjax && !$isPost) {
            $vm = new ViewModel(['title' => 'Editar respuesta', 'form' => $form, 'submitText' => 'Guardar cambios', 'row' => $row, 'question' => $q]);
            $vm->setTemplate('capacitacion/partial/modal-form');
            $vm->setTerminal(true);
            return $vm;
        }
        $q = $this->questionTable->get($questionId);

        if ($isPost) {
            $post = $this->params()->fromPost();
            $post['question_id'] = $questionId;
            $post['is_correct'] = isset($post['is_correct']) ? 1 : 0;

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                unset($data['submit'], $data['csrf']);
                if (($q['type'] ?? 'radio') === 'radio' && (int)$data['is_correct'] === 1) {
                    $this->answerTable->clearCorrectForQuestion($questionId);
                }

                $this->answerTable->save($data);
                if ($isAjax) {
                    $alist = $this->answerTable->fetchByQuestionId($questionId);
                    $vm = new ViewModel(['alist'=>$alist,'qid'=>$questionId]);
                    $vm->setTemplate('capacitacion/partial/answers-list');
                    $vm->setTerminal(true);
                    $html = $this->renderer->render($vm);
                    return new JsonModel(['success'=>true,'questionId'=>$questionId,'answersHtml'=>$html]);
                }
                return $this->redirect()->toRoute('capacitacion-admin/builder', ['id' => $capId]);
            }
        }

        return new ViewModel(['form' => $form, 'question' => $q]);
    }

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($isAjax && !$this->getRequest()->isPost()) {
            $vm = new ViewModel(['title' => 'Nueva respuesta', 'form' => $form, 'submitText' => 'Guardar', 'question' => $q]);
            $vm->setTemplate('capacitacion/partial/modal-form');
            $vm->setTerminal(true);
            return $vm;
        }
        if ($id <= 0) return $this->redirect()->toRoute('capacitacion-admin/cap', ['action' => 'index']);

        $row = $this->answerTable->get($id);
        $q = $this->questionTable->get((int)$row['question_id']);

        $form = $this->answerForm;
        $form->setData($row);
        $form->setAttribute('action', $this->url()->fromRoute('capacitacion-admin/answer', ['action' => 'edit', 'id' => $id]));

        if ($isAjax && !$isPost) {
            $vm = new ViewModel(['title' => 'Editar respuesta', 'form' => $form, 'submitText' => 'Guardar cambios', 'row' => $row, 'question' => $q]);
            $vm->setTemplate('capacitacion/partial/modal-form');
            $vm->setTerminal(true);
            return $vm;
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $post['id'] = $id;
            $post['question_id'] = (int)$row['question_id'];
            $post['is_correct'] = isset($post['is_correct']) ? 1 : 0;

            $form->setData(array_merge($row, $post));
            if ($form->isValid()) {
                $data = $form->getData();
                unset($data['submit'], $data['csrf']);

                if (($q['type'] ?? 'radio') === 'radio' && (int)$data['is_correct'] === 1) {
                    $this->answerTable->clearCorrectForQuestion((int)$row['question_id']);
                }

                $this->answerTable->save($data, $id);
                if ($isAjax) {
                    $alist = $this->answerTable->fetchByQuestionId((int)$row['question_id']);
                    $vm = new ViewModel(['alist' => $alist, 'qid' => (int)$row['question_id']]);
                    $vm->setTemplate('capacitacion/partial/answers-list');
                    $vm->setTerminal(true);
                    $html = $this->renderer->render($vm);
                    return new JsonModel(['success' => true, 'questionId' => (int)$row['question_id'], 'answersHtml' => $html]);
                }
                return $this->redirect()->toRoute('capacitacion-admin/cap', ['action' => 'index']);
            }
        }

        return new ViewModel(['form' => $form, 'row' => $row, 'question' => $q]);
    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        $isAjax = $this->getRequest()->isXmlHttpRequest() || ((string)$this->params()->fromPost('ajax', '') === '1');
        if ($id > 0) {
            $row = $this->answerTable->get($id);
            $qid = (int)$row['question_id'];
            $this->answerTable->delete($id);
            if ($isAjax) {
                $alist = $this->answerTable->fetchByQuestionId($qid);
                $vm = new ViewModel(['alist' => $alist, 'qid' => $qid]);
                $vm->setTemplate('capacitacion/partial/answers-list');
                $vm->setTerminal(true);
                $html = $this->renderer->render($vm);
                return new JsonModel(['success' => true, 'questionId' => $qid, 'answersHtml' => $html]);
            }
        }
        return $this->redirect()->toRoute('capacitacion-admin/cap', ['action' => 'index']);
    }
}
