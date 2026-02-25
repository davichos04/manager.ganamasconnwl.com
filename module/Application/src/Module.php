<?php

declare(strict_types=1);

namespace Application;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Mvc\MvcEvent;

class Module implements ConfigProviderInterface
{

    public function getConfig()
    {
        /** @var array $config */
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }

    // Add this method:
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\UsersTable::class => function ($container) {
                    $tableGateway = $container->get(Model\UsersTableGateway::class);
                    return new Model\UsersTable($tableGateway);
                },
                Model\UsersTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\User());
                    return new TableGateway('users', $dbAdapter, null);
                },
                Model\JUsersTable::class => function ($container) {
                    $tableGateway = $container->get(Model\JUsersTableGateway::class);
                    return new Model\JUsersTable($tableGateway);
                },
                Model\JUsersTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('v_verdevalle_participants', $dbAdapter, null);
                },
                Model\UserAccountTable::class => function ($container) {
                    $tableGateway = $container->get(Model\UserAccountTableGateway::class);
                    return new Model\UserAccountTable($tableGateway);
                },
                Model\UserAccountTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('account_estatus', $dbAdapter, null);
                },
                Model\StoreTransactionsTable::class => function ($container) {
                    $tableGateway = $container->get(Model\StoreTransactionsTableGateway::class);
                    return new Model\StoreTransactionsTable($tableGateway);
                },
                Model\StoreTransactionsTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('j6bp1_store_transactions', $dbAdapter, null);
                },
                Model\SalesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\SalesTableGateway::class);
                    return new Model\SalesTable($tableGateway);
                },
                Model\SalesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('order_details', $dbAdapter, null);
                },
                Model\OrdersTable::class => function ($container) {
                    $tableGateway = $container->get(Model\OrdersTableGateway::class);
                    return new Model\OrdersTable($tableGateway);
                },
                Model\OrdersTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('j6bp1_store_orders_products', $dbAdapter, null);
                },
                Model\FilesTable::class => function ($container) {
                    $tableGateway = $container->get(Model\FilesTableGateway::class);
                    return new Model\FilesTable($tableGateway);
                },
                Model\FilesTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('j6bp1_files', $dbAdapter, null);
                },
                Model\FilesPointsContentTable::class => function ($container) {
                    $tableGateway = $container->get(Model\FilesPointsContentTableGateway::class);
                    $storeTransactionsTable = $container->get(Model\StoreTransactionsTable::class);
                    return new Model\FilesPointsContentTable($tableGateway, $storeTransactionsTable);
                },
                Model\FilesPointsContentTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('j6bp1_files_points_content', $dbAdapter, null);
                },
                Model\FilesEmbarquesUpdateTable::class => function ($container) {
                    $tableGateway = $container->get(Model\FilesEmbarquesUpdateTableGateway::class);
                    $ordersTable = $container->get(Model\OrdersTable::class);
                    return new Model\FilesEmbarquesUpdateTable($tableGateway, $ordersTable);
                },
                Model\FilesEmbarquesUpdateTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('j6bp1_files_embarques_update', $dbAdapter, null);
                },
                Model\ReportUsersTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ReportUsersGateway::class);
                    return new Model\ReportUsersTable($tableGateway);
                },
                Model\ReportUsersGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('report_users', $dbAdapter, null);
                },
                Model\ReportPointsTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ReportPointsGateway::class);
                    return new Model\ReportPointsTable($tableGateway);
                },
                Model\ReportPointsGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('report_points', $dbAdapter, null);
                },
                Model\ReportProductsTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ReportProductsGateway::class);
                    return new Model\ReportProductsTable($tableGateway);
                },
                Model\ReportProductsGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('report_products', $dbAdapter, null);
                },
                Model\ReportSLTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ReportSLGateway::class);
                    return new Model\ReportSLTable($tableGateway);
                },
                Model\ReportSLGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('report_sl', $dbAdapter, null);
                },
                Model\ReportOrderHistoryTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ReportOrderHistoryGateway::class);
                    return new Model\ReportOrderHistoryTable($tableGateway);
                },
                Model\ReportOrderHistoryGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('report_order_history', $dbAdapter, null);
                },
                Model\ReportNewsTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ReportNewsGateway::class);
                    return new Model\ReportNewsTable($tableGateway);
                },
                Model\ReportNewsGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new TableGateway('reporte_noticias', $dbAdapter, null);
                },
                Model\CapacitacionesTable::class => function ($container) {
                    return new Model\CapacitacionesTable(
                        $container->get(AdapterInterface::class)
                    );
                },
            ],
        ];
    }

    public function getControllerConfig()
    {

        return [
            'factories' => [
                Controller\IndexController::class => function ($container) {
                    return new Controller\IndexController(
                        $container->get(Model\JUsersTable::class),
                        // $container->get(Model\WinnersTable::class),
                        // $container->get(Model\CodesTable::class),
                        // $container->get(Model\EmailEventsTable::class)
                    );
                },
                Controller\UsersController::class => function ($container) {
                    return new Controller\UsersController(
                        $container->get(Model\JUsersTable::class)
                        // $container->get(Model\WinnersTable::class),
                        // $container->get(Model\CodesTable::class),
                        // $container->get(Model\EmailEventsTable::class)
                    );
                },
                Controller\PointsController::class => function ($container) {
                    return new Controller\PointsController(
                        $container->get(Model\UserAccountTable::class),
                        $container->get(Model\JUsersTable::class),
                        $container->get(Model\StoreTransactionsTable::class),
                        $container->get(Model\FilesTable::class),
                        $container->get(Model\FilesPointsContentTable::class)
                    );
                },
                Controller\ProductsController::class => function ($container) {
                    return new Controller\ProductsController(
                        $container->get(Model\ReportProductsTable::class)
                    );
                },
                Controller\ReportsController::class => function ($container) {
                    return new Controller\ReportsController(
                        $container->get(Model\ReportUsersTable::class),
                        $container->get(Model\ReportPointsTable::class),
                        $container->get(Model\ReportSLTable::class),
                        $container->get(Model\ReportOrderHistoryTable::class),
                        $container->get(Model\ReportProductsTable::class),
                        $container->get(Model\ReportNewsTable::class)
                    );
                },
                Controller\SalesController::class => function ($container) {
                    return new Controller\SalesController(
                        $container->get(Model\SalesTable::class),
                        $container->get(Model\FilesTable::class),
                        $container->get(Model\OrdersTable::class),
                        $container->get(Model\FilesEmbarquesUpdateTable::class)
                    );
                },
                Controller\LoginController::class => function ($container) {
                    return new Controller\LoginController(
                        $container->get(Model\UsersTable::class),
                        $container->get(Adapter::class)
                    );
                },
                Controller\CapacitacionesController::class => function ($container) {
                    return new Controller\CapacitacionesController(
                        $container->get(Model\CapacitacionesTable::class)
                    );
                },
                Controller\LogoutController::class => function ($container) {
                    return new Controller\LogoutController();
                },
            ],
        ];
    }

}
