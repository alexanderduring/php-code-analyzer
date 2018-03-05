<?php

namespace Application\Controller;

use EmberDb\DocumentManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $fqnParam = $this->getEvent()->getRouteMatch()->getParam('fqn');
        $fqn = urldecode($fqnParam);

        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $namespaces = $documentManager->find('namespaces');

        $names = [];
        $amounts = [];
        foreach ($namespaces as $namespace) {
            $names[] = $namespace->get('name.fqn');
            $amounts[] = $namespace->get('countAllDescendents');
        }

        return array(
            'fqn' => $fqn,
            'namespaces' => $namespaces,
            'names' => $names,
            'amounts' => $amounts
        );
    }



    public function d3BarChartOneAction()
    {
    }



    public function d3BarChartTwoAction()
    {
    }



    public function d3LineChartAction()
    {
    }



    public function d3LineChartDataAction()
    {

        $data = [
            [
                'id' => 'Max.',
                'values' => [
                    ['date' => '2017-01-29', 'temperature' => 6.1],
                    ['date' => '2017-01-30', 'temperature' => 4.2],
                    ['date' => '2017-01-31', 'temperature' => 2.0],
                    ['date' => '2017-02-01', 'temperature' => 2.1]
                ]
            ],
            [
                'id' => 'Min.',
                'values' => [
                    ['date' => '2017-01-29', 'temperature' => -1.8],
                    ['date' => '2017-01-30', 'temperature' => 0.4],
                    ['date' => '2017-01-31', 'temperature' => 0.7],
                    ['date' => '2017-02-01', 'temperature' => 0.1]
                ]
            ]
        ];

        $view = new JsonModel($data);

        return $view;
    }



    public function d3DoughnutChartAction()
    {
    }



    public function d3PartitionChartAction()
    {
    }



    public function d3SunburstChartAction()
    {
    }


    public function fdgAction()
    {
    }



    public function getDataAction()
    {
        $length = rand(1, 12);
        $data = array();
        for ($i = 1; $i <= $length; $i++) {
            $data[] = [
                'label' => 'foo_' . rand(1, 100),
                'count' => rand(1, 100)
            ];
        }

        return new JsonModel($data);
    }



    public function classesAction()
    {
        $fqnParam = $this->getEvent()->getRouteMatch()->getParam('fqn');
        $fqn = urldecode($fqnParam);

        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $classes = $documentManager->find('classes');

        return array(
            'fqn' => $fqn,
            'classes' => $classes
        );
    }



    public function getNamespacesAction()
    {
        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $foundEntries = $documentManager->find('namespaceTree');
        $namespaces = $foundEntries[0];

        $jsonModel = new JsonModel($namespaces->toArray());

        return $jsonModel;
    }



    public function getClassesAction()
    {
        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $classes = $documentManager->find('classes');

        // Add found classes
        $nodes = [];
        $classIndex = []; // To check fast, if a class exists as node
        foreach($classes as $class) {
            $fqn = $class->get('name.fqn');
            $fqnParts = $class->get('name.parts');
            $shortName = array_values(array_slice($fqnParts, -1))[0];
            $group = $fqnParts[0];

            $nodes[] = [
                'id' => $fqn,
                'shortName' => $shortName,
                'group' => $group
            ];

            $classIndex[$fqn] = true;
        }

        // Add found dependencies
        $links = [];
        foreach($classes as $class) {
            $fqn = $class->get('name.fqn');

            // Usages 'new'
            if ($class->has('usages.new')) {
                $usages = $class->get('usages.new');
                foreach($usages as $usageNew) {
                    $clientFqn = $usageNew['context'];
                    $links[] = [
                        'source' => $clientFqn,
                        'target' => $fqn,
                        'value' => 1
                    ];
                    $this->ensureNodeExists($clientFqn, $classIndex, $nodes);
                }
            }

            // Extends
            if ($class->has('extends.name.fqn')) {
                $parentFqn = $class->get('extends.name.fqn');
                $links[] = [
                    'source' => $fqn,
                    'target' => $parentFqn,
                    'value' => 2
                ];

                $this->ensureNodeExists($parentFqn, $classIndex, $nodes);
            }

            // Type Hints
            if ($class->has('usages.type-declaration')) {
                $typeHints = $class->get('usages.type-declaration');
                foreach($typeHints as $typeHint) {
                    $clientFqn = $typeHint['context'];
                    $links[] = [
                        'source' => $clientFqn,
                        'target' => $fqn,
                        'value' => 1
                    ];
                    $this->ensureNodeExists($clientFqn, $classIndex, $nodes);
                }
            }
        }


        $data = [
            'nodes' => $nodes,
            'links' => $links
        ];

        $jsonModel = new JsonModel($data);

        return $jsonModel;
    }



    private function ensureNodeExists($fqn, &$classIndex, &$nodes)
    {
        if (!array_key_exists($fqn, $classIndex)) {
            // Add node
            $nodes[] = [
                'id' => $fqn,
                'shortName' => $fqn,
                'group' => 'External'
            ];
            // Add class to class index
            $classIndex[$fqn] = true;
        }
    }
}
