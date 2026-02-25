<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form\UploadForm;
use Application\Model\FilesTable;
use Application\Model\FilesPointsContentTable;
use Application\Model\UserAccountTable;
use Application\Model\StoreTransactionsTable;
use Application\Model\JUsersTable;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;

class PointsController extends AbstractActionController
{

    private $userAccountTable;
    private $jUsersTable;
    private $storeTransactionsTable;
    private $filesTable;
    private $filesPointsContentTable;

    public function __construct(
        UserAccountTable $userAccountTable,
        JUsersTable $jUsersTable,
        StoreTransactionsTable $storeTransactionsTable,
        FilesTable $filesTable,
        FilesPointsContentTable $filesPointsContentTable
    )
    {
        $this->userAccountTable = $userAccountTable;
        $this->jUsersTable = $jUsersTable;
        $this->storeTransactionsTable = $storeTransactionsTable;
        $this->filesTable = $filesTable;
        $this->filesPointsContentTable = $filesPointsContentTable;
    }

    public function indexAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }
        $identity = ($auth->getIdentity());
        $data = $this->userAccountTable->fetchAll();
        return new ViewModel([
            // 'form' => $form,
            'data' => $data,
            'identity' => $identity
        ]);
    }

    public function detailAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }
        $id = (int) $this->params()->fromRoute('id', '0');
        $detail = [];
        $user = [];
        if ($id > 0) {
            $user = $this->jUsersTable->fetchById($id);
            $userPoints = $this->userAccountTable->fetchByUserId($id);
            $detail = $this->storeTransactionsTable->fetchByUserId($id);
        }
        return new ViewModel(['user' => $user, 'userPoints' => $userPoints, 'detail' => $detail]);
    }

    public function addAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }
        $identity = ($auth->getIdentity());
        $form = new UploadForm('upload-form');
        return new ViewModel([
            'form' => $form,
            'identity' => $identity
        ]);
    }

    public function uploadAction()
    {
        $error = true;
        $auth = new AuthenticationService();
        if ($auth->getIdentity() === null) {
            return $this->redirect()->toRoute('login');
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
                if ($this->validateFile($path)) {
                    $error = false;
                    $fileId = $this->filesTable->createFile($data, $identity->realname, 'PUNTOS');
                    $content = $this->preProcessFile($fileId, $path);
                }
            }
        }
        return new ViewModel(['form' => $form, 'fileContent' => $content, 'fileId' => $fileId, 'error' => $error]);
    }

    public function successAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            echo -2;
            die();
        }
        $identity = ($auth->getIdentity());
        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        if (isset($post['fid'])) {
            $file = $this->filesTable->getFile($post['fid']);
            if ($file['estatus'] === 'Pendiente' && $file['file_type'] === 'PUNTOS') {
                $this->filesPointsContentTable->changeFileStatus($post['fid'], 'Asignando');
                $this->filesTable->changeFileStatus($post['fid'], 'Asignando', $identity->realname);
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo -1;
        }
        die();
    }

    public function cancelAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            echo -2;
            die();
        }
        $identity = ($auth->getIdentity());
        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        if (isset($post['fid'])) {
            $file = $this->filesTable->getFile($post['fid']);
            if ($file['estatus'] === 'Pendiente') {
                $this->filesTable->changeFileStatus($post['fid'], 'Cancelado', $identity->realname);
                $this->filesPointsContentTable->changeFileStatus($post['fid'], 'Cancelado');
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo -1;
        }
        die();
    }

    public function cancelfileAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            echo -2;
            die();
        }
        $identity = ($auth->getIdentity());
        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        if (isset($post['fid'])) {
            $file = $this->filesTable->getFile($post['fid']);
            $this->filesTable->changeFileStatus($post['fid'], 'Error', $identity->realname);
            $this->filesPointsContentTable->changeFileStatus($post['fid'], 'Error');
            echo 1;
        } else {
            echo -1;
        }
        die();
    }

    private function readCsv($path)
    {
        return $this->CsvImport($path, false);
    }

    private function preProcessFile($fileId, $path)
    {
        $content = $this->readCsv($path);
        $return = [];
        $first = true;
        foreach ($content as $row) {
            if ($first) {
                $first = false;
                continue;
            }
            $user = $this->userAccountTable->fetchByUsername($row[0]);
            $name = 'Usuario no participante';
            $profile = 'N/A';
            $id = $oldBalance = 0;
            if ($user != false) {
                $id = (int) $user->id;
                $name = $user->name;
                $profile = $user->profile;
                $tempOldBalance = $this->filesPointsContentTable->find($id, $fileId);
                $oldBalance = $tempOldBalance > 0 ? $tempOldBalance : (int) $user->available;
            }

            $data = [
                'user_id' => $id,
                'file_id' => $fileId,
                'name' => $name,
                'cellphone' => $row[0],
                'profile' => $profile,
                'month' => $row[1],
                'concept' => $row[2],
                'old_account_balance' => $oldBalance,
                'new_points' => $row[3],
                'new_account_balance' => $id > 0 ? $oldBalance + $row[3] : 0
            ];
            $this->filesPointsContentTable->add($data);
            array_push($return, $data);
        }
        return $return;
    }
    
    private function validateFile($path)
    {
        $content = $this->readCsv($path);
        $i = true;
        if (count($content) < 1) {
            return false;
        }
        foreach ($content as $row) {
            if ($i) {
                $i = false;
                continue;
            }
            // $row[0]=>Cellphone
            // $row[1]=>Mes
            // $row[2]=>Concepto
            // $row[3]=>Puntos
            if (isset($row[0]) && isset($row[1]) && isset($row[2]) && isset($row[3])) {
                if ((int) $row[3] > 100000) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }
}