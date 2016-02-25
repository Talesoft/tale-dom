<?php

namespace Tale\Dom\Xml;

use Tale\Dom\Manipulator as DomManipulator;

class Manipulator extends DomManipulator
{

    public static function getElementClassName()
    {

        return Element::class;
    }
}