<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\ReportOrderHistoryTable;
use Application\Model\ReportSLTable;
use Application\Model\ReportPointsTable;
use Application\Model\ReportUsersTable;
use Application\Model\ReportProductsTable;
use Application\Model\ReportNewsTable;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;

class ReportsController extends AbstractActionController
{

    private $reportUsersTable;
    private $reportPointsTable;
    private $reportSLTable;
    private $reportOrderHistoryTable;
    private $reportProductsTable;
    private $reportNewsTable;

    public function __construct(
        ReportUsersTable $reportUsersTable,
        ReportPointsTable $reportPointsTable,
        ReportSLTable $reportSLTable,
        ReportOrderHistoryTable $reportOrderHistoryTable,
        ReportProductsTable $reportProductsTable,
        ReportNewsTable $reportNewsTable
    ) {
        $this->reportUsersTable = $reportUsersTable;
        $this->reportPointsTable = $reportPointsTable;
        $this->reportSLTable = $reportSLTable;
        $this->reportOrderHistoryTable = $reportOrderHistoryTable;
        $this->reportProductsTable = $reportProductsTable;
        $this->reportNewsTable = $reportNewsTable;
    }

    public function indexAction()
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }
        $identity = ($auth->getIdentity());
        return new ViewModel([
            'identity' => $identity
        ]);
    }

    public function usersAction()
    {
        $auth = new AuthenticationService();
        if ($auth->getIdentity() === null) {
            return $this->redirect()->toRoute('login');
        }
        $data = $this->reportUsersTable->fetchAll();

        $view = new ViewModel(['data' => $data]);
        $view->setTerminal(true);
        return $view;
    }

    public function slAction()
    {
        $auth = new AuthenticationService();
        if ($auth->getIdentity() === null) {
            return $this->redirect()->toRoute('login');
        }
        $data = $this->reportSLTable->fetchAll();

        $view = new ViewModel(['data' => $data]);
        $view->setTerminal(true);
        return $view;
    }

    public function pointsAction()
    {
        $auth = new AuthenticationService();
        if ($auth->getIdentity() === null) {
            return $this->redirect()->toRoute('login');
        }
        $data = $this->reportPointsTable->fetchAll();

        $view = new ViewModel(['data' => $data]);
        $view->setTerminal(true);
        return $view;
    }

    public function productsAction()
    {
        $auth = new AuthenticationService();
        if ($auth->getIdentity() === null) {
            return $this->redirect()->toRoute('login');
        }
        $data = $this->reportProductsTable->fetchAll();

        $view = new ViewModel(['data' => $data]);
        $view->setTerminal(true);
        return $view;
    }

    public function orderhistoryAction()
    {
        $auth = new AuthenticationService();
        if ($auth->getIdentity() === null) {
            return $this->redirect()->toRoute('login');
        }
        $data = $this->reportOrderHistoryTable->fetchAll();

        $view = new ViewModel(['data' => $data]);
        $view->setTerminal(true);
        return $view;
    }

    public function newshistoryAction()
    {
        $auth = new AuthenticationService();
        if ($auth->getIdentity() === null) {
            return $this->redirect()->toRoute('login');
        }
        $data = $this->reportNewsTable->fetchAll();

        $view = new ViewModel(['data' => $data]);
        $view->setTerminal(true);
        return $view;
    }
}
