<?php

namespace Example\House;

class House extends AbstractHouse
{
    public function __construct()
    {
        echo "This is a house " . parent::LOCATION;
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
