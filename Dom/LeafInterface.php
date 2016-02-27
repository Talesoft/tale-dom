<?php

namespace Tale\Dom;

use Tale\Tree\LeafInterface as TreeLeafInterface;

interface LeafInterface extends TreeLeafInterface
{

    public function format(Formatter $formatter = null, $level = null);
    public function __toString();
}