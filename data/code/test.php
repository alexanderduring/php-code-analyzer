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

$var = new Foo();
$var->bar();

$var2 = new Baz\Foo();