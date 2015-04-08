<?php

abstract class AbstractHouse
{
    public function openDoor()
    {
        $door = new AnotherThing();
        $door->open();
    }
}
