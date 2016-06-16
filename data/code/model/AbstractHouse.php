<?php

namespace Example\House;

abstract class AbstractHouse
{
    public function openDoor()
    {
        $door = new AnotherThing();
        $door->open();
    }
}
