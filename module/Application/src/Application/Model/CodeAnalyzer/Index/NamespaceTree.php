<?php

namespace Application\Model\CodeAnalyzer\Index;

class NamespaceTree
{
    private $namespaceTree;



    public function __construct(array $namespaces)
    {
        $this->namespaceTree = $this->buildNamespaceTree('\\', $namespaces);
    }



    public function toArray()
    {
        return $this->namespaceTree;
    }


    /**
     * This is a recursive method of building a namespace tree,
     * by first building all its subnamespace trees and then
     * putting them togetger.
     *
     * @param $fqn
     * @param array $namespaces
     * @return array
     */
    private function buildNamespaceTree($fqn, array $namespaces)
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
            $subNamespace = $this->buildNamespaceTree($subFqn, $namespaces);

            $subNamespaces[$subFqn] = $subNamespace;
        }
        $parentNamespace['subNamespaces'] = $subNamespaces;

        return $parentNamespace;
    }
}