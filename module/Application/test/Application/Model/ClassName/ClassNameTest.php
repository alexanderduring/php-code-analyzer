<?php

namespace Application\Model\ClassName;

use PHPUnit\Framework\TestCase;

class ClassNameTest extends TestCase
{
    public function providerGetFullyQualifiedName()
    {
        $defaultPreconditions = [];
        $defaultExpectations = [];

        $testCases = [
            'Without namespace, without leading backslash' => [
                'preconditions' => ['fqn' => 'FooClass'],
                'expectations' => ['fullyQualifiedName' => '\\FooClass']
            ],
            'Without namespace, with leading backslash' => [
                'preconditions' => ['fqn' => '\\FooClass'],
                'expectations' => ['fullyQualifiedName' => '\\FooClass']
            ],
            'With namespace, without leading backslash' => [
                'preconditions' => ['fqn' => 'Foo\\BarClass'],
                'expectations' => ['fullyQualifiedName' => '\\Foo\\BarClass']
            ],
            'With namespace, with leading backslash' => [
                'preconditions' => ['fqn' => '\\Foo\\BarClass'],
                'expectations' => ['fullyQualifiedName' => '\\Foo\\BarClass']
            ],
        ];

        // Merge test data with default data
        foreach ($testCases as &$testCase) {
            $testCase['preconditions'] = array_merge($defaultPreconditions, $testCase['preconditions']);
            $testCase['expectations'] = array_merge($defaultExpectations, $testCase['expectations']);
        }

        return $testCases;
    }


    /**
     * @dataProvider providerGetFullyQualifiedName
     */
    public function testGetFullyQualifiedName($preconditions, $expectations)
    {
        $className = new ClassName($preconditions['fqn']);
        $fullyQualifiedName = $className->getFullyQualifiedName();

        $this->assertEquals($expectations['fullyQualifiedName'], $fullyQualifiedName);
    }



    public function providerGetBaseName()
    {
        $defaultPreconditions = [];
        $defaultExpectations = [];

        $testCases = [
            'Without namespace' => [
                'preconditions' => ['fqn' => 'FooClass'],
                'expectations' => ['baseName' => 'FooClass']
            ],
            'With namespace' => [
                'preconditions' => ['fqn' => 'Foo\\Bar\\BazClass'],
                'expectations' => ['baseName' => 'BazClass']
            ],
        ];

        // Merge test data with default data
        foreach ($testCases as &$testCase) {
            $testCase['preconditions'] = array_merge($defaultPreconditions, $testCase['preconditions']);
            $testCase['expectations'] = array_merge($defaultExpectations, $testCase['expectations']);
        }

        return $testCases;
    }


    /**
     * @dataProvider providerGetBaseName
     */
    public function testGetBaseName($preconditions, $expectations)
    {
        $className = new ClassName($preconditions['fqn']);
        $baseName = $className->getBaseName();

        $this->assertEquals($expectations['baseName'], $baseName);
    }



    public function providerGetNamespace()
    {
        $defaultPreconditions = [];
        $defaultExpectations = [];

        $testCases = [
            'Without namespace' => [
                'preconditions' => ['fqn' => 'FooClass'],
                'expectations' => ['namespace' => '\\']
            ],
            'With namespace' => [
                'preconditions' => ['fqn' => 'Foo\\Bar\\BazClass'],
                'expectations' => ['namespace' => 'Foo\\Bar']
            ],
        ];

        // Merge test data with default data
        foreach ($testCases as &$testCase) {
            $testCase['preconditions'] = array_merge($defaultPreconditions, $testCase['preconditions']);
            $testCase['expectations'] = array_merge($defaultExpectations, $testCase['expectations']);
        }

        return $testCases;
    }


    /**
     * @dataProvider providerGetNamespace
     */
    public function testGetNamespace($preconditions, $expectations)
    {
        $className = new ClassName($preconditions['fqn']);
        $namespace = $className->getNamespace();

        $this->assertEquals($expectations['namespace'], $namespace);
    }



    public function providerMatchesNamespaceFilter()
    {
        $defaultPreconditions = [];
        $defaultExpectations = [];

        $testCases = [
            // Without wildcard
            '\\ matches BazClass' => [
                'preconditions' => [
                    'fqn' => 'BazClass',
                    'filter' => '\\'
                ],
                'expectations' => [
                    'matches' => true
                ]
            ],
            '\\ does not match Foo\\Bar\\BazClass' => [
                'preconditions' => [
                    'fqn' => 'Foo\\Bar\\BazClass',
                    'filter' => '\\'
                ],
                'expectations' => [
                    'matches' => false
                ]
            ],
            'Foo\\Bar does not match BazClass' => [
                'preconditions' => [
                    'fqn' => 'BazClass',
                    'filter' => 'Foo\\Bar'
                ],
                'expectations' => [
                    'matches' => false
                ]
            ],
            'Foo\\Bar matches Foo\\Bar\\BazClass' => [
                'preconditions' => [
                    'fqn' => 'Foo\\Bar\\BazClass',
                    'filter' => 'Foo\\Bar'
                ],
                'expectations' => [
                    'matches' => true
                ]
            ],
            // With wildcard
            '* matches FooClass' => [
                'preconditions' => [
                    'fqn' => 'FooClass',
                    'filter' => '*'
                ],
                'expectations' => [
                    'matches' => true
                ]
            ],
            '* matches Bar\\FooClass' => [
                'preconditions' => [
                    'fqn' => 'Bar\\FooClass',
                    'filter' => '*'
                ],
                'expectations' => [
                    'matches' => true
                ]
            ],
            '\\* matches FooClass' => [
                'preconditions' => [
                    'fqn' => 'FooClass',
                    'filter' => '\\*'
                ],
                'expectations' => [
                    'matches' => true
                ]
            ],
            '\\* matches Bar\\FooClass' => [
                'preconditions' => [
                    'fqn' => 'Bar\\FooClass',
                    'filter' => '\\*'
                ],
                'expectations' => [
                    'matches' => true
                ]
            ],
            'Bar\\* does not matches FooClass' => [
                'preconditions' => [
                    'fqn' => 'FooClass',
                    'filter' => 'Bar\\*'
                ],
                'expectations' => [
                    'matches' => false
                ]
            ],
            'Bar\\* matches Bar\\FooClass' => [
                'preconditions' => [
                    'fqn' => 'Bar\\FooClass',
                    'filter' => 'Bar\\*'
                ],
                'expectations' => [
                    'matches' => true
                ]
            ],
            'Bar\\* matches Bar\\Baz\\FooClass' => [
                'preconditions' => [
                    'fqn' => 'Bar\\Baz\\FooClass',
                    'filter' => 'Bar\\*'
                ],
                'expectations' => [
                    'matches' => true
                ]
            ],
        ];

        // Merge test data with default data
        foreach ($testCases as &$testCase) {
            $testCase['preconditions'] = array_merge($defaultPreconditions, $testCase['preconditions']);
            $testCase['expectations'] = array_merge($defaultExpectations, $testCase['expectations']);
        }

        return $testCases;
    }


    /**
     * @dataProvider providerMatchesNamespaceFilter
     */
    public function testMatchesNamespaceFilter($preconditions, $expectations)
    {
        $className = new ClassName($preconditions['fqn']);
        $matches = $className->matchesNamespaceFilter($preconditions['filter']);

        $this->assertEquals($expectations['matches'], $matches);
    }
}
