<?php

namespace Example;

interface Barable
{
    public function bar();
}



class Foo implements Barable
{
    /**
     * @return boolean
     */
    public function bar()
    {
        return true;
    }
}



class Thing
{
    private $anotherThing;
    public $name = 'AnotherThing';

    public function __construct(AnotherThing $anotherThing)
    {
        $this->anotherThing = $anotherThing;
    }
}



class AnotherThing
{
    static public $name = 'AnotherThing';

    public $property;

    public function open()
    {
        echo "open!";
    }
}



class Main
{
    public function __construct()
    {
        // Instantiation in an assignment
        $var = new Foo();
        $var->bar();

        // Instantiation in an array
        $list = array(
            'instance' => new Foo()
        );

        // Instantiation in a function call
        $var2 = new Thing(new AnotherThing());

        // Instantiation with class name variable
        $className = 'AnotherThing';
        $var3 = new $className();

        // Instantiation with static class variable
        $var4 = new AnotherThing::$name();

        // Instantiation with object property
        $var5 = new $var2->name();
    }
}

$var6 = new Main();
$var7 = new Thing(new AnotherThing());