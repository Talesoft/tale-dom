<?php

namespace Tale\Dom\Html;

use Tale\Dom\Manipulator as DomManipulator;

class Manipulator extends DomManipulator
{

    public static function getElementClassName()
    {

        return Element::class;
    }
}