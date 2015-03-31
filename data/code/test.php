<?php

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

    public function __construct(AnotherThing $anotherThing)
    {
        $this->anotherThing = $anotherThing;
    }
}



class AnotherThing
{
    public $property;
}



// Instantiation in an assignment
$var = new Foo();
$var->bar();

// Instantiation in an assignment
$var2 = new Baz\Foo();

// Instantiation in an array
$list = array(
    'instance' => new Foo()
);

// Instantiation in a function call
$var3 = new Thing(new AnotherThing());
