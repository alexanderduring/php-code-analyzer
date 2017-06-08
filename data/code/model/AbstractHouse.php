<?php

namespace Example\House;

abstract class AbstractHouse
{
    const LOCATION = 'in the middle of the street';

    public function openDoor()
    {
        $text = self::LOCATION;

        $door = new AnotherThing();
        $door->open();
    }
}
