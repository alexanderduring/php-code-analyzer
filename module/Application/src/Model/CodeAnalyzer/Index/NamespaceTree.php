<?php

namespace Application\Model\CodeAnalyzer\Index;

class NamespaceTree
{
    private $namespaceTree = [];



    public function toArray(): array
    {
        return $this->namespaceTree;
    }


    /*
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



    public function addClass(array $namespace)
    {
        // Add class to namespace
        $subTree = &$this->namespaceTree;
        foreach ($namespace as $level) {
            if (!array_key_exists($level, $subTree)) {
                $subTree[$level] = [];
            }
            $subTree = &$subTree[$level];
        }
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
