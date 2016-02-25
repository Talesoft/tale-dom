<?php

namespace Tale\Dom;

use Tale\Tree\Leaf;
use Tale\Tree\NodeInterface;

/**
 * Class Text
 *
 * @package Tale\Dom
 */
class Text extends Leaf
{

    /**
     * @var string
     */
    private $_text;


    /**
     * Text constructor.
     *
     * @param string $text
     * @param NodeInterface $parent
     */
    public function __construct($text, NodeInterface $parent = null)
    {
        parent::__construct($parent);

        $this->_text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {

        return $this->_text;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->_text;
    }
}