<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\ReportProductsTable;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;

class ProductsController extends AbstractActionController {

    private $reportProductsTable;

    public function __construct(
            ReportProductsTable $reportProductsTable
    ) {
        $this->reportProductsTable = $reportProductsTable;
    }

    public function indexAction() {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }
        $identity = ($auth->getIdentity());
        $data = $this->reportProductsTable->fetchAll();
        return new ViewModel([
            // 'form' => $form,
            'data' => $data,
            'identity' => $identity
        ]);
    }

}
