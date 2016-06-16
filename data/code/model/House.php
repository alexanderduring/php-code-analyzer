<?php

namespace Example\House;

class House
{
    public function __construct()
    {
        echo "This is a house!";
    }



    public function openDoor()
    {
        echo "Open door.";
    }



    public function instantiateTheThird(array $classes)
    {
        $instance = new $classes['third'];

        return $instance;
    }
}
