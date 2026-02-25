<?php

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'application' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/application[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'participants' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/participants[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\UsersController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'products' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/products[/:action]',
                    'defaults' => [
                        'controller' => Controller\ProductsController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'points' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/points[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\PointsController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'sales' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/sales[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\SalesController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'capacitaciones' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/capacitaciones[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\CapacitacionesController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'reports' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/reports[/:action]',
                    'defaults' => [
                        'controller' => Controller\ReportsController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'login' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/user/login[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\LoginController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'logout' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/user/logout',
                    'defaults' => [
                        'controller' => Controller\LogoutController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    /*
    'service_manager' => [
    'aliases' => [
    Model\PostRepositoryInterface::class => Model\PostRepository::class,
    ],
    'factories' => [
    Model\PostRepository::class => InvokableFactory::class,
    ],
    ],
    'controllers' => [
    'factories' => [
    Controller\IndexController::class => InvokableFactory::class,
    ],
    ],
    */
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\CleanString::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
        ],
        'aliases' => [
            'cleanString' => View\Helper\CleanString::class,
        ],
    ],
];
