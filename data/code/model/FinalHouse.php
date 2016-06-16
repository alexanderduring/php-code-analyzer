<?php

namespace Example\House;

final class FinalHouse extends House
{
    public final function openDoor()
    {
        parent::openDoor();
        echo "That's final!";
    }

}