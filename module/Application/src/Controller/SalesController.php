<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form\UploadForm;
use Application\Model\FilesEmbarquesUpdateTable;
use Application\Model\FilesTable;
use Application\Model\OrdersTable;
use Application\Model\SalesTable;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;

class SalesController extends AbstractActionController
{

    private $salesTable;
    private $filesTable;
    private $ordersTable;
    private $filesEmbarquesUpdateTable;

    public function __construct(
        SalesTable $salesTable,
        FilesTable $filesTable,
        OrdersTable $ordersTable,
        FilesEmbarquesUpdateTable $filesEmbarquesUpdateTable
    )
    {
        $this->salesTable = $salesTable;
        $this->filesTable = $filesTable;
        $this->ordersTable = $ordersTable;
        $this->filesEmbarquesUpdateTable = $filesEmbarquesUpdateTable;
    }

    public function indexAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }
        $identity = ($auth->getIdentity());
        $data = $this->salesTable->fetchAll();
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
        if ($id > 0) {
            $detail = $this->salesTable->fetchByUserId($id);
        }
        return new ViewModel(['detail' => $detail]);
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

    public function downloadAction()
    {
        $auth = new AuthenticationService();
        if ($auth->getIdentity() === null) {
            return $this->redirect()->toRoute('login');
        }
        $data = $this->salesTable->fetchAllPending();

        $view = new ViewModel(['data' => $data]);
        $view->setTerminal(true);
        return $view;
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
                    $fileId = $this->filesTable->createFile($data, $identity->realname, 'ESTATUS');
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
            if ($file['estatus'] === 'Pendiente') {
                $this->filesTable->changeFileStatus($post['fid'], 'Procesado', $identity->realname);
                $this->filesEmbarquesUpdateTable->changeFileStatus($post['fid'], 'Procesado');
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
                $this->filesEmbarquesUpdateTable->changeFileStatus($post['fid'], 'Cancelado');
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
            $this->filesEmbarquesUpdateTable->changeFileStatus($post['fid'], 'Error');
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
            $order = $this->salesTable->fetchByADN($row[1]);
            $comments = 'Id único de canje no encontrado';
            $orderId = $userId = $total = 0;
            if ($order != null) {
                $orderId = (int) $order->order_id;
                $userId = (int) $order->id;
                $total = (int) $order->price;
                $comments = "";
            }

            $data = [
                'order_detail_id' => $orderId,
                'user_id' => $userId,
                'file_id' => $fileId,
                'cellphone' => $row[0],
                'adn' => $row[1],
                'new_status' => $row[2],
                'guide' => $row[3],
                'carrier' => $row[4],
                'picked_at' => $row[5],
                'release_at' => $row[6],
                'who_recibes' => $row[7],
                'lastupdated_at' => $row[8],
                'sales_date' => $row[9],
                'comments' => $comments,
                'estatus' => 'Pendiente'
            ];
            $this->filesEmbarquesUpdateTable->add($data);
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
            // $row[0]=>CELULAR
            // $row[1]=>ID ÚNICO DE CANJE
            // $row[2]=>NUEVO ESTATUS,
            // $row[3]=>NÚMERO DE GUÍA,
            // $row[4]=>PAQUETERÍA,
            // $row[5]=>FECHA DE RECOLECCIÓN,
            // $row[6]=>FECHA DE ENTREGA PAQUETERÍA,
            // $row[7]=>PERSONA QUIEN RECIBE,
            // $row[8]=>FECHA DE ACTUALIZACIÓN,
            // $row[9]=>FECHA DE CANJE
            if (!isset($row[0]) || !isset($row[1]) || !isset($row[2]) || !isset($row[3]) || !isset($row[4]) || !isset($row[5]) || !isset($row[6]) || !isset($row[7]) || !isset($row[8]) || !isset($row[9])) {
                return false;
            }
        }
        return true;
    }
}