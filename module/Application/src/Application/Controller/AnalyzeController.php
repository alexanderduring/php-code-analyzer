<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;

class AnalyzeController extends AbstractActionController
{
    private $classes = array();
    private $assignments = array();
    private $newExpressions = array();



    public function runAction()
    {
        $code = file_get_contents('data/code/test.php');
        $parser = new Parser(new Lexer());

        try {
            $nodes = $parser->parse($code);
        } catch (PhpParser\Error $exception) {
            echo 'Parse Error: ', $exception->getMessage();
        }

        foreach ($nodes as $node) {
            $this->checkNode($node);
        }

        $this->report();

        return;
    }



    /**
     * @param Node $node
     */
    private function checkNode(Node $node)
    {
        if ($node->getType() == 'Stmt_Class') {
            $className = $node->name;
            $class = array('name' => $className);
            $this->classes[$className] = $class;
        }

        if ($node->getType() == 'Expr_Assign') {
            $this->assignments[] = $node;
            $this->checkNode($node->expr);
        }

        if ($node->getType() == 'Expr_New') {
            $this->newExpressions[] = $node;
        }
    }



    private function report()
    {
        echo "Instanzierungen\n";
        echo "---------------\n";

        foreach ($this->newExpressions as $new) {

            $classNameNode = $new->class;
            $className = implode('\\', $classNameNode->parts);

            if (array_key_exists($className, $this->classes)) {
                $classIsKnown = true;
            } else {
                $classIsKnown = false;
            }

            $classIsKnownText = $classIsKnown ? '' : ' (unbekannt)';

            $lineNumber = $new->getLine();
            echo "Klasse " . $className . $classIsKnownText .  ", Zeile " . $lineNumber . "\n";
        }
    }
}
