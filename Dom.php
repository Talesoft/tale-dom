<?php

namespace Tale;

use Tale\Dom\Element;
use Tale\Dom\Html\Element as HtmlElement;
use Tale\Dom\Manipulator;
use Tale\Dom\Html\Manipulator as HtmlManipulator;

final class Dom
{

    public function fromString($input, $encoding = null, $html = false)
    {

        if ($html)
            return HtmlElement::fromString($input, $encoding);

        return Element::fromString($input, $encoding);
    }

    public function fromFile($path, $encoding = null, $html = false)
    {

        if ($html)
            return HtmlElement::fromFile($path, $encoding);

        return Element::fromFile($path, $encoding);
    }

    public function manipulate($elements = null, $html = false)
    {

        if ($html)
            return new HtmlManipulator($elements);

        return new Manipulator($elements);
    }

    public function manipulateFile($path, $html = false)
    {

        $markup = file_get_contents($path);

        if ($html)
            return new HtmlManipulator($markup);

        return new Manipulator($markup);
    }
}