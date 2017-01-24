<?php

namespace Application\Model\CodeAnalyzer\Index;

class NamespaceTree
{
    private $namespaceTree = array();



    public function toArray()
    {
        return $this->namespaceTree;
    }


    /**
     * This is a recursive method of building a namespace tree,
     * by first building all its subnamespace trees and then
     * putting them together.
     *
     * @param $fqn
     * @param array $namespaces
     * @return array
     */
    private function buildNamespaceTreeOld($fqn, array $namespaces)
    {
        // Find $fqn namespace
        $parentNamespace = null;
        foreach ($namespaces as $key => $namespace) {
            if ($namespace['name']['fqn'] === $fqn) {
                $parentNamespace = $namespace;

                // Remove $fqn namespace from array of namespaces (for performance reasons)
                array_diff_key($namespaces, array($key => $namespace));
            }
        }

        // Throw an exception if $fqn was not found
        if (is_null($parentNamespace)) {
            throw new \Exception('Namespace ' . $fqn . ' could not be found.');
        }

        // Copy subnamespace names from keys to values. Keys were used during analyzing
        // because of the efficient 'unique' feature of keys.
        $parentNamespace['subNamespaces'] = array_keys($parentNamespace['subNamespaces']);

        // Build subnamespaces and add them the parent namespace
        $subNamespaces = array();

        foreach ($parentNamespace['subNamespaces'] as $subFqn) {
            $subNamespace = $this->buildNamespaceTreeOld($subFqn, $namespaces);

            $subNamespaces[$subFqn] = $subNamespace;
        }
        $parentNamespace['subNamespaces'] = $subNamespaces;

        return $parentNamespace;
    }



    public function addClass($class)
    {
        $nameParts = $class->get('name.parts');
        $fqn = $class->get('name.fqn');
        $shortClassName = $nameParts[count($nameParts) - 1];
        $numLines = $class->get('endLine') - $class->get('startLine');

        // get namespace
        $namespaceParts = $nameParts;
        array_splice($namespaceParts, -1);
        array_unshift($namespaceParts, '\\');

        // Add class to namespace
        $subTree = &$this->namespaceTree;
        foreach ($namespaceParts as $namespacePart) {
            if (!array_key_exists($namespacePart, $subTree)) {
                $subTree[$namespacePart] = array(
                    'name' => $namespacePart,
                    'children' => array()
                );
            }
            $subTree = &$subTree[$namespacePart]['children'];
        }

        $subTree[] = array(
            'name' => array(
                'short' => $shortClassName,
                'fqn' => $fqn
            ),
            'numClasses' => 1,
            'numLines' => $numLines
        );
    }



    private function namespaceExists($namespaceName, $children)
    {
        $exists = false;
        foreach ($children as $child) {
            if ($child['name'] === $namespaceName) {
                $exists = true;
            }
        }

        return $exists;
    }



    private function getNamespace($namespaceParts, &$tree)
    {
        if (count($namespaceParts) > 1) {
            // Then usual case
            $parentNamespaceParts = $namespaceParts;
            array_splice($parentNamespaceParts, -1);

            // Get parent namespace
            $parentNamespace = $this->getNamespace($parentNamespaceParts, $tree);

            // Check if parent namespace has children
            if (array_key_exists('children', $parentNamespace)) {
            }

        } else {
            // namespace is \

        }
    }
}
