<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\CapacitacionesTable;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class CapacitacionesController extends AbstractActionController
{
    private CapacitacionesTable $capacitacionesTable;

    public function __construct(CapacitacionesTable $capacitacionesTable)
    {
        $this->capacitacionesTable = $capacitacionesTable;
    }

    public function indexAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }

        return new ViewModel([
            'identity' => $auth->getIdentity(),
        ]);
    }

    public function dataAction()
    {
        $identity = $this->requireIdentity();
        if ($identity === null) {
            return new JsonModel(['success' => false, 'message' => 'Sesión expirada']);
        }

        return new JsonModel([
            'success' => true,
            'data' => $this->capacitacionesTable->fetchTree(),
            'products' => $this->capacitacionesTable->fetchRewardProducts(),
        ]);
    }

    public function saveCapacitacionAction()
    {
        $identity = $this->requireIdentity();
        if ($identity === null) {
            return new JsonModel(['success' => false, 'message' => 'Sesión expirada']);
        }

        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        $files = $request->getFiles()->toArray();
        $id = !empty($post['id']) ? (int) $post['id'] : null;

        $mediaType = $post['media_type'] ?? 'video';
        $mediaUrl = trim((string) ($post['media_url'] ?? ''));
        if (($mediaType === 'image' || $mediaType === 'pdf') && !empty($files['media_file']['name'])) {
            $mediaUrl = $this->storeUploadedFile($files['media_file'], 'media');
        }

        $thumbnail = trim((string) ($post['thumbnail'] ?? ''));
        if (!empty($files['thumbnail_file']['name'])) {
            $thumbnail = $this->storeUploadedFile($files['thumbnail_file'], 'thumbs');
        }

        $title = trim((string) ($post['title'] ?? ''));
        $alias = trim((string) ($post['alias'] ?? ''));
        if ($alias === '') {
            $alias = $this->slugify($title);
        }

        $data = [
            'title' => $title,
            'alias' => $alias,
            'description' => $post['description'] ?? null,
            'media_type' => $mediaType,
            'media_url' => $mediaUrl,
            'thumbnail' => $thumbnail ?: null,
            'expires_at' => !empty($post['expires_at']) ? date('Y-m-d H:i:s', strtotime((string) $post['expires_at'])) : null,
            'published' => !empty($post['published']) ? 1 : 0,
        ];

        if ($id) {
            $data['modified_by'] = (int) $identity->id;
        } else {
            $data['created_by'] = (int) $identity->id;
        }

        $this->capacitacionesTable->saveCapacitacion($data, $id);

        return new JsonModel(['success' => true, 'message' => 'Capacitación guardada']);
    }

    public function saveQuizAction()
    {
        $identity = $this->requireIdentity();
        if ($identity === null) {
            return new JsonModel(['success' => false, 'message' => 'Sesión expirada']);
        }

        $post = $this->getRequest()->getPost()->toArray();
        $id = !empty($post['id']) ? (int) $post['id'] : null;
        $rewardMode = $post['reward_mode'] ?? 'none';

        $data = [
            'capacitacion_id' => (int) ($post['capacitacion_id'] ?? 0),
            'title' => trim((string) ($post['title'] ?? '')),
            'max_attempts' => $post['max_attempts'] !== '' ? (int) $post['max_attempts'] : null,
            'pass_score' => $post['pass_score'] !== '' ? (int) $post['pass_score'] : null,
            'published' => !empty($post['published']) ? 1 : 0,
            'reward_mode' => $rewardMode,
            'reward_product_id' => $rewardMode === 'product' ? (int) ($post['reward_product_id'] ?? 0) : null,
            'reward_points' => $rewardMode === 'points' ? (int) ($post['reward_points'] ?? 0) : null,
            'reward_limit' => (int) ($post['reward_limit'] ?? 0),
            'reward_awarded_count' => (int) ($post['reward_awarded_count'] ?? 0),
        ];

        $this->capacitacionesTable->saveQuiz($data, $id);
        return new JsonModel(['success' => true, 'message' => 'Quiz guardado']);
    }

    public function saveQuestionAction()
    {
        $identity = $this->requireIdentity();
        if ($identity === null) {
            return new JsonModel(['success' => false, 'message' => 'Sesión expirada']);
        }

        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        $files = $request->getFiles()->toArray();
        $id = !empty($post['id']) ? (int) $post['id'] : null;

        $image = trim((string) ($post['image'] ?? ''));
        if (!empty($files['image_file']['name'])) {
            $image = $this->storeUploadedFile($files['image_file'], 'questions');
        }

        $data = [
            'quiz_id' => (int) ($post['quiz_id'] ?? 0),
            'question_text' => trim((string) ($post['question_text'] ?? '')),
            'image' => $image ?: null,
            'ordering' => (int) ($post['ordering'] ?? 0),
            'type' => $post['type'] ?? 'radio',
            'published' => !empty($post['published']) ? 1 : 0,
        ];

        $this->capacitacionesTable->saveQuestion($data, $id);
        return new JsonModel(['success' => true, 'message' => 'Pregunta guardada']);
    }

    public function saveAnswerAction()
    {
        $identity = $this->requireIdentity();
        if ($identity === null) {
            return new JsonModel(['success' => false, 'message' => 'Sesión expirada']);
        }

        $post = $this->getRequest()->getPost()->toArray();
        $id = !empty($post['id']) ? (int) $post['id'] : null;

        $data = [
            'question_id' => (int) ($post['question_id'] ?? 0),
            'answer_text' => trim((string) ($post['answer_text'] ?? '')),
            'is_correct' => !empty($post['is_correct']) ? 1 : 0,
        ];

        $this->capacitacionesTable->saveAnswer($data, $id);
        return new JsonModel(['success' => true, 'message' => 'Respuesta guardada']);
    }

    public function deleteCapacitacionAction()
    {
        return $this->deleteNode('capacitacion');
    }

    public function deleteQuizAction()
    {
        return $this->deleteNode('quiz');
    }

    public function deleteQuestionAction()
    {
        return $this->deleteNode('question');
    }

    public function deleteAnswerAction()
    {
        return $this->deleteNode('answer');
    }

    private function deleteNode(string $type): JsonModel
    {
        $identity = $this->requireIdentity();
        if ($identity === null) {
            return new JsonModel(['success' => false, 'message' => 'Sesión expirada']);
        }

        $post = $this->getRequest()->getPost()->toArray();
        $id = (int) ($post['id'] ?? 0);
        if ($id <= 0) {
            return new JsonModel(['success' => false, 'message' => 'ID inválido']);
        }

        if ($type === 'capacitacion') {
            $this->capacitacionesTable->deleteCapacitacion($id);
        } elseif ($type === 'quiz') {
            $this->capacitacionesTable->deleteQuiz($id);
        } elseif ($type === 'question') {
            $this->capacitacionesTable->deleteQuestion($id);
        } else {
            $this->capacitacionesTable->deleteAnswer($id);
        }

        return new JsonModel(['success' => true, 'message' => 'Elemento eliminado']);
    }

    private function requireIdentity()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return null;
        }
        return $auth->getIdentity();
    }

    private function storeUploadedFile(array $file, string $folder): string
    {
        $basePath = getcwd() . '/public/uploads/capacitaciones/' . $folder;
        if (!is_dir($basePath)) {
            mkdir($basePath, 0775, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9\.\-_]/', '-', (string) $file['name']);
        $fileName = uniqid('', true) . '-' . $safeName;
        $target = $basePath . '/' . $fileName;

        move_uploaded_file((string) $file['tmp_name'], $target);

        return '/uploads/capacitaciones/' . $folder . '/' . $fileName;
    }

    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-zA-Z0-9]+/', '-', (string) $text);
        $text = strtolower(trim((string) $text, '-'));

        return $text !== '' ? $text : uniqid('cap-');
    }
}
