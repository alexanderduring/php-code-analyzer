<?php

class Foo
{
    /**
     * @return boolean
     */
    public function bar()
    {
        return true;
    }
}

$var = new Foo();
$var->bar();

$var2 = new Baz\Foo();