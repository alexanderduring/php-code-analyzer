<?php

namespace Application\Controller;

use Application\Model\ClassName\ClassName;
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
        $filters = [];

        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $classes = $documentManager->find('classes');

        $nodes = [];
        $links = [];
        $classIndex = []; // To check fast, if a class exists as node

        // Add found classes
        foreach($classes as $class) {
            $fqn = $class->get('name.fqn');
            if ($this->passesNamespaceFilters($fqn, $filters)) {
                $fqnParts = $class->get('name.parts');
                $type = $class->get('type');
                $group = $fqnParts[0];
                $this->addNode($fqn, $type, $group, $classIndex, $nodes);
            }
        }

        // Add found dependencies
        foreach($classes as $class) {
            $fqn = $class->get('name.fqn');

            if ($this->passesNamespaceFilters($fqn, $filters)) {
                // Usages 'new'
                if ($class->has('usages.new')) {
                    $usages = $class->get('usages.new');
                    foreach($usages as $usageNew) {
                        $clientFqn = $usageNew['context'];
                        if ($this->passesNamespaceFilters($clientFqn, $filters)) {
                            $this->addLink($clientFqn, $fqn, 1, $links);
                            $this->addNode($clientFqn, 'unknown', 'New', $classIndex, $nodes);
                        }
                    }
                }

                // Extends
                if ($class->has('extends.name.fqn')) {
                    $parentFqn = $class->get('extends.name.fqn');
                    if ($this->passesNamespaceFilters($parentFqn, $filters)) {
                        $this->addLink($fqn, $parentFqn, 5, $links);
                        $this->addNode($parentFqn, 'unknown', 'External', $classIndex, $nodes);
                    }
                }

                // Type Hints
                if ($class->has('usages.type-declaration')) {
                    $typeHints = $class->get('usages.type-declaration');
                    foreach($typeHints as $typeHint) {
                        $clientFqn = $typeHint['context'];
                        $this->addLink($clientFqn, $fqn, 1, $links);
                        $this->addNode($clientFqn, 'unknown', 'External', $classIndex, $nodes);
                    }
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



    private function addNode(string $fqn, string $type, string $group, array &$classIndex, array &$nodes)
    {
        if (!array_key_exists($fqn, $classIndex)) {

            // Add node
            $nodes[] = [
                'id' => $fqn,
                'shortName' => $fqn,
                'type' => str_replace(' ', '-', $type),
                'group' => $group
            ];

            // Add class to class index
            $classIndex[$fqn] = true;
        }
    }



    private function addLink(string $sourceFqn, string $targetFqn, int $value, array &$links)
    {
        $links[] = [
            'source' => $sourceFqn,
            'target' => $targetFqn,
            'value' => $value
        ];
    }



    private function passesNamespaceFilters($fqn, $filters)
    {
        $className = new ClassName($fqn);

        $matches = false;
        foreach($filters as $filter) {
            if ($className->matchesNamespaceFilter($filter)) {
                $matches = true;
                break;
            }
        }

        $passes = !$matches;

        return $passes;
    }
}
