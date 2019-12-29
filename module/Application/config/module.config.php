<?php

return [
    'controllers' => [
        'invokables' => [
            Application\Controller\IndexController::class => Application\Controller\IndexController::class,
            Application\Controller\ProjectController::class => Application\Controller\ProjectController::class
        ],
        'factories' => [
            Application\Controller\AnalyzeController::class => Application\Controller\AnalyzeControllerFactory::class
        ]
    ],
    'service_manager' => [
        'factories' => [
            Application\Model\CodeAnalyzer\CodeAnalyzer::class => Application\Model\CodeAnalyzer\CodeAnalyzerFactory::class
        ]
    ],
    'view_helpers' => [
        'factories' => [
            'menu' => Application\View\Helper\MenuFactory::class
        ]
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',

            'application/viewhelper/menu' => __DIR__ . '/../view/viewhelper/menu.phtml'
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy'
        ]
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'index'
                    ]
                ]
            ],
            'd3-bar-chart-one' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/d3-bar-chart-one',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3BarChartOne'
                    ]
                ]
            ],
            'd3-bar-chart-two' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/d3-bar-chart-two',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3BarChartTwo'
                    ]
                ]
            ],
            'd3-line-chart' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/d3-line-chart',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3LineChart'
                    ]
                ]
            ],
            'd3-line-chart-data' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/d3-line-chart-data',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3LineChartData'
                    ]
                ]
            ],
            'd3-doughnut-chart' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/d3-doughnut-chart',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3DoughnutChart'
                    ]
                ]
            ],
            'd3-partition-chart' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/d3-partition-chart',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3PartitionChart'
                    ]
                ]
            ],
            'd3-sunburst-chart' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/d3-sunburst-chart',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'd3SunburstChart'
                    ]
                ]
            ],
            'd3-get-data' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/d3-get-data',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'getData'
                    ]
                ]
            ],
            'classes' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/classes[/:fqn]',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'classes'
                    ]
                ]
            ],
            'force-directed-graph' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/fdg',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'fdg'
                    ]
                ]
            ],
            'get-namespaces' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/get-namespaces',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'getNamespaces'
                    ]
                ]
            ],
            'get-classes' => [
                'type' => Zend\Router\Http\Segment::class,
                'options' => [
                    'route'    => '/get-classes',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'getClasses'
                    ]
                ]
            ]
        ]
    ],
    'console' => [
        'router' => [
            'routes' => [
                'analyzer-run' => [
                    'options' => [
                        'route'    => 'run [--ignore=] <path>',
                        'defaults' => [
                            'controller' => Application\Controller\AnalyzeController::class,
                            'action'     => 'run'
                        ]
                    ]
                ],
                'analyzer-report' => [
                    'options' => [
                        'route'    => 'report',
                        'defaults' => [
                            'controller' => Application\Controller\AnalyzeController::class,
                            'action'     => 'report'
                        ]
                    ]
                ],
                'project-list' => [
                    'options' => [
                        'route'    => 'project list',
                        'defaults' => [
                            'controller' => Application\Controller\ProjectController::class,
                            'action'     => 'list'
                        ]
                    ]
                ],
                'project-new' => [
                    'options' => [
                        'route'    => 'project new [--name=] <path>',
                        'defaults' => [
                            'controller' => Application\Controller\ProjectController::class,
                            'action'     => 'new'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'menu' => [
        'Menü' => [
            'Home' => 'home',
            'Classes' => 'classes',
            'Dependency' => 'force-directed-graph'
        ],
        'D3.js Tutorial' => [
            'Bar Chart I (div)' => 'd3-bar-chart-one',
            'Bar Chart II (svg)' => 'd3-bar-chart-two',
            'Line Chart (svg)' => 'd3-line-chart',
            'Doughnut Chart' => 'd3-doughnut-chart',
            'Partition Chart' => 'd3-partition-chart',
            'Sunburst Chart' => 'd3-sunburst-chart'
        ]
    ]
];
