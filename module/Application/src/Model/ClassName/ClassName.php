<?php

namespace Application\Model\ClassName;

use Exception;

class ClassName
{
    private $baseName;
    private $namespaceParts;



    public function __construct(string $fullyQualifiedName)
    {
        if ($fullyQualifiedName == '') {
            throw new Exception('Parameter $fullyQualifiedName must not be empty.');
        }

        // Remove leading backslash
        $fullyQualifiedName = ltrim($fullyQualifiedName, '\\');

        // Split into namespace and base name
        $nameParts = explode('\\', $fullyQualifiedName);
        $this->baseName = array_pop($nameParts);
        $this->namespaceParts = $nameParts;
    }



    public function getFullyQualifiedName(): string
    {
        $fullyQualifiedNameParts = $this->namespaceParts;
        $fullyQualifiedNameParts[] = $this->baseName;
        $fullyQualifiedName = '\\' . implode('\\', $fullyQualifiedNameParts);

        return $fullyQualifiedName;
    }



    public function getBaseName(): string
    {
        return $this->baseName;
    }



    public function getNamespace(): string
    {
        $namespace = $this->isRootNamespace() ? '\\' : implode('\\', $this->namespaceParts);

        return $namespace;
    }



    public function getNamespaceAsArray(): array
    {
        return $this->namespaceParts;
    }



    /*
     * Filter syntax:
     *
     * | *     | All classes                                                                     |
     * | \     | All classes in the root namespace              |
     * | Foo   | All classes in namespace Foo\                  |
     * | Foo\* | All classes with namespace starting with Foo\,
     * |         including the one with just Foo\               |
     *
     */
    public function matchesNamespaceFilter(string $filterExpression): bool
    {
        $filter = $this->parseFilterExpression($filterExpression);
        $namespace = $this->namespaceParts;

        // remember wildcard and then remove it from filter
        if (!empty($filter) && $filter[count($filter)-1] === '*') {
            $hasWildcard = true;
            array_pop($filter);
        } else {
            $hasWildcard = false;
        }

        $sizeFilter = count($filter);
        $sizeNamespace = count($namespace);

        if ($sizeFilter > $sizeNamespace) {
            // if filter has more parts than namespace, matching is impossible.
            $matches = false;
        } else {
            $matches = true;
            foreach($namespace as $index => $namespacePart) {

                if (array_key_exists($index, $filter)) {
                    if ($namespacePart !== $filter[$index]) {
                        $matches = false;
                        break;
                    }
                } else {
                    if ($hasWildcard) {
                        break;
                    } else {
                        $matches = false;
                        break;
                    }
                }
            }
        }

        return $matches;
    }



    private function isRootNamespace(): bool
    {
        $isRootNamespace = empty($this->namespaceParts);

        return $isRootNamespace;
    }



    private function parseFilterExpression(string $filterExpression): array
    {
        // Trim backslashes
        $filterExpression = trim($filterExpression, '\\');

        // Separate filter parts
        if ($filterExpression === '') {
            $filter = [];
        } else {
            $filter = explode('\\', $filterExpression);
        }

        return $filter;
    }
}