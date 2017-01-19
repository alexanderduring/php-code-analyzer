<?php

return array(
    'controllers' => array(
        'invokables' => array(
            Application\Controller\IndexController::class => Application\Controller\IndexController::class
        ),
        'factories' => array(
            Application\Controller\AnalyzeController::class => Application\Controller\AnalyzeControllerFactory::class
        )
    ),
    'service_manager' => array(
        'factories' => array(
            Application\Model\CodeAnalyzer\CodeAnalyzer::class => Application\Model\CodeAnalyzer\CodeAnalyzerFactory::class
        )
    ),
    'view_helpers' => array(
        'factories' => array(
            'Menu' => Application\View\Helper\MenuFactory::class
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

            'application/viewhelper/menu' => __DIR__ . '/../view/viewhelper/menu.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => Zend\Mvc\Router\Http\Segment::class,
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'index'
                    )
                )
            ),
            'd3-bar-chart-one' => array(
                'type' => Zend\Mvc\Router\Http\Segment::class,
                'options' => array(
                    'route'    => '/d3-bar-chart-one',
                    'defaults' => array(
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3BarChartOne'
                    )
                )
            ),
            'd3-bar-chart-two' => array(
                'type' => Zend\Mvc\Router\Http\Segment::class,
                'options' => array(
                    'route'    => '/d3-bar-chart-two',
                    'defaults' => array(
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3BarChartTwo'
                    )
                )
            ),
            'd3-doughnut-chart' => array(
                'type' => Zend\Mvc\Router\Http\Segment::class,
                'options' => array(
                    'route'    => '/d3-doughnut-chart',
                    'defaults' => array(
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3DoughnutChart'
                    )
                )
            ),
            'd3-sunburst-chart' => array(
                'type' => Zend\Mvc\Router\Http\Segment::class,
                'options' => array(
                    'route'    => '/d3-sunburst-chart',
                    'defaults' => array(
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3SunburstChart'
                    )
                )
            ),
            'd3-get-data' => array(
                'type' => Zend\Mvc\Router\Http\Segment::class,
                'options' => array(
                    'route'    => '/d3-get-data',
                    'defaults' => array(
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'getData'
                    )
                )
            ),
            'classes' => array(
                'type' => Zend\Mvc\Router\Http\Segment::class,
                'options' => array(
                    'route'    => '/classes[/:fqn]',
                    'defaults' => array(
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'classes'
                    )
                )
            ),
            'get-namespaces' => array(
                'type' => Zend\Mvc\Router\Http\Segment::class,
                'options' => array(
                    'route'    => '/get-namespaces',
                    'defaults' => array(
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'getNamespaces'
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
                            'controller' => Application\Controller\AnalyzeController::class,
                            'action'     => 'run'
                        )
                    )
                ),
                'analyzer-report' => array(
                    'options' => array(
                        'route'    => 'report',
                        'defaults' => array(
                            'controller' => Application\Controller\AnalyzeController::class,
                            'action'     => 'report'
                        )
                    )
                )
            )
        )
    ),
    'menu' => array(
        'Menü' => array(
            'Home' => 'home',
            'Classes' => 'classes'
        ),
        'D3.js Tutorial' => array(
            'Bar Chart I (div)' => 'd3-bar-chart-one',
            'Bar Chart II (svg)' => 'd3-bar-chart-two',
            'Doughnut Chart' => 'd3-doughnut-chart',
            'Sunburst Chart' => 'd3-sunburst-chart'
        )
    )
);
