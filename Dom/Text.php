<?php

namespace Tale\Dom;

use Tale\Tree\Leaf;

/**
 * Class Text
 *
 * @package Tale\Dom
 */
class Text extends Leaf implements LeafInterface
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

    public function format(Formatter $formatter = null, $level = null)
    {

        $formatter = $formatter ?: new Formatter();
        return $formatter->formatText($this->_text, $level);
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->format();
    }
}