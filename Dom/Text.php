<?php

namespace Tale\Dom;

use Tale\Tree\Leaf;
use Tale\Tree\NodeInterface;

class Text extends Leaf
{

    /**
     * @var string
     */
    private $_text;

    public function __construct($text, NodeInterface $parent = null)
    {
        parent::__construct($parent);

        $this->_text = $text;
    }

    public function getText()
    {

        return $this->_text;
    }

    public function __toString()
    {

        return $this->_text;
    }
}