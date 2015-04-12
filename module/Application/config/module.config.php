<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Analyze' => 'Application\Controller\AnalyzeController'
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'CodeAnalyzer' => 'Application\Model\CodeAnalyzer\CodeAnalyzerFactory'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index'
                    )
                )
            )
        )
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'analyzer-run' => array(
                    'options' => array(
                        'route'    => 'run [--ignore=] <path>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Analyze',
                            'action'     => 'run'
                        )
                    )
                ),
                'analyzer-report' => array(
                    'options' => array(
                        'route'    => 'report',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Analyze',
                            'action'     => 'report'
                        )
                    )
                )
            )
        )
    )
);
