<?php
declare(strict_types=1);

namespace Capacitacion;

return [
    'router' => [
        'routes' => [
            'capacitacion-admin' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/admin/capacitacion',
                    'defaults' => [
                        'controller' => \Capacitacion\Controller\CapacitacionController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'builder' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/builder[/:id]',
                            'defaults' => [
                                'controller' => \Capacitacion\Controller\BuilderController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'cap' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/cap[/:action[/:id]]',
                            'defaults' => [
                                'controller' => \Capacitacion\Controller\CapacitacionController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'quiz' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/quiz[/:action[/:id]]',
                            'defaults' => [
                                'controller' => \Capacitacion\Controller\QuizController::class,
                                'action' => 'edit',
                            ],
                        ],
                    ],
                    'question' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/question[/:action[/:id]]',
                            'defaults' => [
                                'controller' => \Capacitacion\Controller\QuestionController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'answer' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/answer[/:action[/:id]]',
                            'defaults' => [
                                'controller' => \Capacitacion\Controller\AnswerController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            \Capacitacion\Controller\CapacitacionController::class => \Capacitacion\Controller\Factory\CapacitacionControllerFactory::class,
            \Capacitacion\Controller\BuilderController::class => \Capacitacion\Controller\Factory\BuilderControllerFactory::class,
            \Capacitacion\Controller\QuizController::class => \Capacitacion\Controller\Factory\QuizControllerFactory::class,
            \Capacitacion\Controller\QuestionController::class => \Capacitacion\Controller\Factory\QuestionControllerFactory::class,
            \Capacitacion\Controller\AnswerController::class => \Capacitacion\Controller\Factory\AnswerControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            \Capacitacion\Service\RewardRules::class => \Capacitacion\Service\Factory\InvokableFactory::class,
            \Capacitacion\Service\FileUploadService::class => \Capacitacion\Service\Factory\FileUploadServiceFactory::class,

            \Capacitacion\Model\Table\CapacitacionTable::class => \Capacitacion\Model\Factory\TableFactory::class,
            \Capacitacion\Model\Table\QuizTable::class         => \Capacitacion\Model\Factory\TableFactory::class,
            \Capacitacion\Model\Table\QuestionTable::class     => \Capacitacion\Model\Factory\TableFactory::class,
            \Capacitacion\Model\Table\AnswerTable::class       => \Capacitacion\Model\Factory\TableFactory::class,
            \Capacitacion\Model\Table\ProductTable::class      => \Capacitacion\Model\Factory\TableFactory::class,

            \Capacitacion\Form\CapacitacionForm::class => \Capacitacion\Service\Factory\InvokableFactory::class,
            \Capacitacion\Form\QuestionForm::class => \Capacitacion\Service\Factory\InvokableFactory::class,
            \Capacitacion\Form\AnswerForm::class => \Capacitacion\Service\Factory\InvokableFactory::class,
            \Capacitacion\Form\QuizForm::class => \Capacitacion\Form\Factory\QuizFormFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'capacitacion' => [
        'table_prefix' => 'j6bp1_',
        // Base absoluta donde vive /images (no /public)
        'images_abs_base_dir' => '/var/www/test.ganamasconnwl.com/images',
        // Base URL pública
        'images_url_base' => '/images',
        // Subcarpeta del módulo dentro de images
        'images_module_dir' => 'capacitacion',
        'max_upload_bytes' => 2 * 1024 * 1024,
        'allowed_mime' => ['image/jpeg','image/png','image/gif','image/webp'],
    ],
];
