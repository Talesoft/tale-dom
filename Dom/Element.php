<?php

namespace Tale\Dom;

use Tale\Tree\LeafInterface;
use Tale\Tree\Node;
use Tale\Tree\NodeInterface;

/**
 * Class Element
 * @package Tale\Dom
 */
class Element extends Node
{

    /**
     *
     */
    const ATTRIBUTE_ID = 'id';
    /**
     *
     */
    const ATTRIBUTE_CLASS = 'class';

    /**
     * @var string
     */
    private $_name;
    /**
     * @var array|null
     */
    private $_attributes;

    /**
     * @param string             $name
     * @param array|null         $attributes
     * @param NodeInterface|null $parent
     * @param array|null         $children
     */
    public function __construct($name, array $attributes = null, NodeInterface $parent = null, array $children = null)
    {
        parent::__construct($parent, $children);

        $this->_name = $name;
        $this->_attributes = $attributes ? $attributes : [];
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->_name;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {

        $this->_name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAttributes()
    {

        return count($this->_attributes) > 0;
    }

    /**
     * @return array|null
     */
    public function getAttributes()
    {

        return $this->_attributes;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes)
    {

        $this->_attributes = $attributes;

        return $this;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {

        return isset($this->_attributes[$name]);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getAttribute($name)
    {

        return $this->_attributes[$name];
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setAttribute($name, $value)
    {

        $this->_attributes[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function removeAttribute($name)
    {

        unset($this->_attributes[$name]);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasId()
    {

        return $this->hasAttribute(self::ATTRIBUTE_ID);
    }

    /**
     * @return mixed
     */
    public function getId()
    {

        return $this->getAttribute(self::ATTRIBUTE_ID);
    }

    /**
     * @param $id
     *
     * @return \Tale\Dom\Element
     */
    public function setId($id)
    {

        return $this->setAttribute(self::ATTRIBUTE_ID, $id);
    }

    /**
     * @return bool
     */
    public function hasClasses()
    {

        return $this->hasAttribute(self::ATTRIBUTE_CLASS);
    }

    /**
     * @return mixed
     */
    public function getClasses()
    {

        return $this->getAttribute(self::ATTRIBUTE_CLASS);
    }

    /**
     * @param $classes
     *
     * @return \Tale\Dom\Element
     */
    public function setClasses($classes)
    {

        return $this->setAttribute(self::ATTRIBUTE_CLASS, $classes);
    }

    /**
     * @return array
     */
    public function getClassArray()
    {

        if (!$this->hasClasses())
            return [];

        return explode(' ', $this->getClasses());
    }

    /**
     * @param array $classes
     *
     * @return \Tale\Dom\Element
     */
    public function setClassArray(array $classes)
    {

        return $this->setClasses(implode(' ', $classes));
    }

    /**
     * @param $class
     *
     * @return bool
     */
    public function hasClass($class)
    {

        if (!$this->hasClasses())
            return false;

        return in_array($class, $this->getClassArray());
    }

    /**
     * @param $class
     *
     * @return \Tale\Dom\Element
     */
    public function appendClass($class)
    {

        $classes = $this->getClassArray();
        $classes[] = $class;

        return $this->setClassArray($classes);
    }

    /**
     * @param $class
     *
     * @return \Tale\Dom\Element
     */
    public function prependClass($class)
    {

        $classes = $this->getClassArray();
        array_unshift($classes, $class);

        return $this->setClassArray($classes);
    }

    /**
     * @param $class
     *
     * @return $this|\Tale\Dom\Element
     */
    public function removeClass($class)
    {

        $classes = $this->getClassArray();
        $idx = array_search($class, $classes);

        if ($idx === false)
            return $this;

        unset($classes[$idx]);

        return $this->setClassArray($classes);
    }

    /**
     * @return \Generator
     */
    public function getChildElements()
    {

        return $this->findChildren(function(LeafInterface $leaf) {

            return $leaf instanceof static;
        }, 1);
    }

    /**
     * @return array
     */
    public function getChildElementArray()
    {

        return iterator_to_array($this->getChildElements());
    }

    /**
     * @return \Generator
     */
    public function findTexts($depth = null)
    {

        return $this->findChildren(function(LeafInterface $leaf) {

            return $leaf->isInstanceOf(static::getTextClassName());
        }, $depth);
    }

    /**
     * @return array
     */
    public function findTextArray($depth = null)
    {

        return iterator_to_array($this->findTexts($depth));
    }

    /**
     * @return string
     */
    public function getText()
    {

        return implode(' ', $this->findTextArray());
    }

    /**
     * @param $text
     *
     * @return $this
     */
    public function setText($text)
    {

        return $this->removeChildren()->appendChild(new Text($text));
    }

    /**
     * @param $selector
     *
     * @return bool
     * @throws \Exception
     */
    public function matches($selector)
    {

        $selector = $selector instanceof Selector ? $selector : Selector::fromString($selector);

        return $selector->matches($this);
    }

    /**
     * @param      $selector
     * @param null $depth
     *
     * @return \Generator
     * @throws \Exception
     */
    public function findElements($selector, $depth = null)
    {

        $selector = $selector instanceof Selector ? $selector : Selector::fromString($selector);

        return $this->find(function(LeafInterface $leaf) use ($selector) {

            if (!($leaf instanceof Element))
                return false;

            return $selector->matches($leaf);
        }, $depth);
    }

    /**
     * @param      $selector
     * @param null $depth
     *
     * @return array
     */
    public function findElementArray($selector, $depth = null)
    {

        return iterator_to_array($this->findElements($selector, $depth));
    }

    /**
     * @param $selectors
     *
     * @return \Generator
     */
    public function query($selectors)
    {

        if (is_array($selectors))
            $selectors = implode(',', $selectors);

        //We add a , to the selector to trigger the "," selector below and flush the results
        $selectors = preg_split('/(,| |>)/', "$selectors,", -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);

        $depth = null;
        /** @var Element[] $currentSet */
        $currentSet = [$this];
        foreach ($selectors as $selector) {

            $selector = trim($selector);

            if (empty($selector))
                continue;

            if ($selector === '>') {

                $depth = 1;
                continue;
            }

            if ($selector === ',') {

                foreach ($currentSet as $child)
                    yield $child;

                $depth = null;
                $currentSet = [$this];
                continue;
            }

            foreach ($currentSet as $child)
                $currentSet = $child->findElements($selector, $depth);

            $depth = null;
        }
    }

    /**
     * @param $selectors
     *
     * @return array
     */
    public function queryArray($selectors)
    {

        return iterator_to_array($this->query($selectors));
    }

    /**
     * @param bool $pretty
     * @param bool $requireCloseTag
     * @param null $selfClosingTags
     *
     * @return string
     */
    public function getString($pretty = false, $requireCloseTag = false, $selfClosingTags = null)
    {

        $newLine = $pretty ? "\n" : '';
        $indent = $pretty ? '  ' : '';
        $lineLimit = 60;
        $selfClosingTags = $selfClosingTags ? $selfClosingTags : [];

        $name = $this->_name;
        /** @var Element[] $children */
        $children = $this->getChildren();
        $attributes = $this->getAttributes();
        $childCount = count($children);
        $hasChildren = $childCount > 0;
        $isSelfClosing = in_array(strtolower($name), array_map('strtolower', $selfClosingTags));
        $strlen = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';

        $str = "<$name";

        if (count($attributes) > 0) {

            $str .= ' '.implode(' ', array_map(function($name, $value) {

                    return "$name=\"$value\"";
                }, array_keys($attributes), $attributes));
        }

        if (!$hasChildren) {

            if ($isSelfClosing)
                return $str.'>';

            if ($requireCloseTag)
                return $str."></$name>";

            return $str.' />';
        }

        $str .= $newLine;
        $inlineText = false;
        foreach ($children as $child) {

            if ($child instanceof Text) {

                $text = str_replace(["\r", "\n", "\t"], ' ', $child->getText());

                $inlineText = $childCount === 1;

                $str .= $strlen($text) > $lineLimit
                      ? $indent.wordwrap($text, $lineLimit, "$newLine$indent").$newLine
                      : ($inlineText ? $indent : '').$text.($inlineText > 1 ? "\n" : '');
                continue;
            }

            if ($child instanceof Element) {

                $str .= $indent.$child->getString($pretty, $requireCloseTag, $selfClosingTags).$newLine;
            }
        }

        return $str.($inlineText ? $indent : '')."</$name>";
    }

    /**
     * @param                      $selector
     * @param \Tale\Tree\Node|null $parent
     * @param array|null           $children
     *
     * @return static
     * @throws \Exception
     */
    public static function fromSelector($selector, Node $parent = null, array $children = null)
    {

        $selector = $selector instanceof Selector ? $selector : Selector::fromString($selector);

        $tag = $selector->getName();
        $el = new static($tag ? $tag : 'div', $selector->getAttributes(), $parent, $children);

        if ($id = $selector->getId())
            $el->setId($id);

        if ($classes = $selector->getClasses())
            foreach ($classes as $class)
                $el->appendClass($class);

        return $el;
    }

    /**
     * @return mixed
     */
    public static function getTextClassName()
    {

        return Text::class;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getString();
    }
}