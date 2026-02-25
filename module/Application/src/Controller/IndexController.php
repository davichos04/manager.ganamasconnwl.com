<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form\UploadForm;
use Application\Model\JUsersTable;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController {

    private $jUsersTable;
    private $winnersTable;
    private $codesTable;
    private $emailEventsTable;

    public function __construct(
            JUsersTable $jUsersTable
            // , WinnersTable $winnersTable, CodesTable $codesTable, EmailEventsTable $emailEventsTable
    ) {
        $this->jUsersTable = $jUsersTable;
//         $this->winnersTable = $winnersTable;
        // $this->codesTable = $codesTable;
        // $this->emailEventsTable = $emailEventsTable;
    }

    public function indexAction() {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }
        $identity = ($auth->getIdentity());
        $userStats = $this->jUsersTable->getStats();
        return new ViewModel([
            'identity' => $identity,
            'userStats' => $userStats
        ]);
    }

    public function uploadAction() {
        $error = true;
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            //return $this->redirect()->toRoute('login');
        }
        $identity = ($auth->getIdentity());
        $content = [];
        $form = new UploadForm('upload-form');
        $request = $this->getRequest();
        $fileId = 0;
        if ($request->isPost()) {
            // Make certain to merge the $_FILES info!
            $post = array_merge_recursive(
                    $request->getPost()->toArray(), $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                $path = $data['csv-file']['tmp_name'];
                $content = $this->readCsv($path);
                $file = new File();
                $file->filename = $data['csv-file']['name'];
                $file->realFilename = $data['csv-file']['tmp_name'];

                if (count($content) > 1 && $this->validateFile($content)) {
                    $error = false;
                }
                $file->estatus = $error ? 'Error en el archivo' : 'Pendiente de autorizar';
                $fileId = $this->filesTable->createFile($file, $identity->realname);
                if (!$error) {
                    $this->winnersTable->saveWinners($content, $fileId);
                }
            }
        }
        return new ViewModel(['form' => $form, 'fileContent' => $content, 'fileId' => $fileId, 'error' => $error]);
    }

    public function successAction() {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            // echo -2;
            // die();
        }
        $identity = ($auth->getIdentity());
        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        if (isset($post['fid'])) {
            $file = $this->filesTable->getFile($post['fid']);
            if ($file['estatus'] === 'Pendiente de autorizar') {
                $this->winnersTable->changeFileStatus($post['fid'], 'Pendiente de asignar premios');
                $this->filesTable->changeFileStatus($post['fid'], 'Pendiente de asignar premios', $identity->realname);
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo -1;
        }
        die();
    }

    public function cancelAction() {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            // echo -2;
            // die();
            // return $this->redirect()->toRoute('login');
        }
        $identity = ($auth->getIdentity());

        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        if (isset($post['fid'])) {
            $file = $this->filesTable->getFile($post['fid']);
            if ($file['estatus'] === 'Pendiente de autorizar') {
                $this->filesTable->changeFileStatus($post['fid'], 'Cancelado', $identity->realname);
                $this->winnersTable->changeFileStatus($post['fid'], 'Cancelado');
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo -1;
        }
        die();
    }

    public function detailAction() {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            // return $this->redirect()->toRoute('login');
        }
        $id = (int) $this->params()->fromRoute('id', '0');
        $file = null;
        $content = [];
        if ($id > 0) {
            $file = $this->filesTable->getFile($id);
            $content = $this->winnersTable->getByFileId($id);
        }
        return new ViewModel(['fileContent' => $content, 'file' => $file]);
    }

    public function emailsdetailAction() {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            // return $this->redirect()->toRoute('login');
        }
        $content = []; //$this->winnersTable->fetchAll();
        return new ViewModel(['fileContent' => $content]);
    }

    public function reportAction() {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            // return $this->redirect()->toRoute('login');
        }
        $id = (int) $this->params()->fromRoute('id', '0');
        $file = null;
        $content = [];
        if ($id > 0) {
            $content = $this->winnersTable->fechByPromoId($id);
        }
        $view = new ViewModel(['fileContent' => $content]);
        $view->setTerminal(true);
        return $view;
    }

    public function chartcodesAction() {
        $content = [];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            // $content = $this->codesTable->getStats();
        }
        $view = new ViewModel(['data' => $content]);
        $view->setTerminal(true);
        return $view;
    }
    
    public function chartusersAction() {
        $content = [];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            // $content = $this->codesTable->getStats();
        }
        $view = new ViewModel(['data' => $content]);
        $view->setTerminal(true);
        return $view;
    }

    public function reportcodesAction() {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            // return $this->redirect()->toRoute('login');
        }
        $content = [];
        $content = $this->codesTable->getStats();
        $view = new ViewModel(['content' => $content]);
        $view->setTerminal(true);
        return $view;
    }

    public function webhookAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost()->toArray();
            $emailId = $this->winnersTable->addEmailEvent($post);
            $this->emailEventsTable->addEmailEvent($emailId, $post);
        }
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    private function readCsv($path) {
        return $this->CsvImport($path, false);
    }

    private function validateFile($content) {
        $i = true;
        foreach ($content as $row) {
            if ($i) {
                $i = false;
                continue;
            }
            if (isset($row[0]) && isset($row[1]) && isset($row[2]) && isset($row[3])) {
                return true;
            }
        }
        return false;
    }

}
