<?php

namespace Tale\Dom;

use Tale\Tree\Node;

/**
 * Class Element
 * @package Tale\Dom
 */
class Element extends Node implements ElementInterface
{

    /**
     *
     */
    const ID_ATTRIBUTE = 'id';
    /**
     *
     */
    const CLASS_ATTRIBUTE = 'class';

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

        return $this->hasAttribute(static::ID_ATTRIBUTE);
    }

    /**
     * @return mixed
     */
    public function getId()
    {

        return $this->getAttribute(static::ID_ATTRIBUTE);
    }

    /**
     * @param $id
     *
     * @return \Tale\Dom\Element
     */
    public function setId($id)
    {

        return $this->setAttribute(static::ID_ATTRIBUTE, $id);
    }

    /**
     * @return bool
     */
    public function hasClasses()
    {

        return $this->hasAttribute(static::CLASS_ATTRIBUTE);
    }

    /**
     * @return mixed
     */
    public function getClasses()
    {

        return $this->getAttribute(static::CLASS_ATTRIBUTE);
    }

    /**
     * @param $classes
     *
     * @return \Tale\Dom\Element
     */
    public function setClasses($classes)
    {

        return $this->setAttribute(static::CLASS_ATTRIBUTE, $classes);
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

        return $this->setClasses(implode(' ', array_map('trim', $classes)));
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
     * @param int $depth
     *
     * @return \Generator
     */
    public function findTexts($depth = null)
    {

        return $this->findChildren(function(LeafInterface $leaf) {

            return $leaf->isInstanceOf(static::getTextClassName());
        }, $depth);
    }

    /**
     * @param int $depth
     *
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
     * @param Selector|string $selector
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
     * @param Selector|string $selector
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
     * @param Selector|string $selector
     * @param null $depth
     *
     * @return array
     */
    public function findElementArray($selector, $depth = null)
    {

        return iterator_to_array($this->findElements($selector, $depth));
    }

    /**
     * @param string $selectors
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
     * @param Formatter $formatter
     * @param int       $level
     *
     * @return string
     * @throws \Exception
     */
    public function format(Formatter $formatter = null, $level = null)
    {

        $formatter = $formatter ? $formatter : new Formatter();
        return $formatter->formatElement($this, $level);
    }

    public static function fromString($string, $encoding = null)
    {

        $parserClass = static::getParserClassName();
        /** @var Parser $parser */
        $parser = new $parserClass($encoding);

        return $parser->parse($string);
    }

    public static function fromFile($path, $encoding = null)
    {

        $parserClass = static::getParserClassName();
        /** @var Parser $parser */
        $parser = new $parserClass($encoding);

        return $parser->parseFile($path);
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

    public static function getParserClassName()
    {

        return Parser::class;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->format();
    }
}