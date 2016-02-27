<?php

namespace Tale\Dom;

use Exception;
use IteratorAggregate;
use Countable;
use Traversable;

//Just try this: var_dump($m->html('[lang="de"]')->html->parent->body->parent)      :)
/**
 * Class Manipulator
 *
 * @property Manipulator $elements
 * @property Manipulator $first
 * @property Manipulator $last
 * @property Manipulator $parent
 * @property Manipulator $root
 *
 * @method $this setAttribute(string $key, string $value)
 * @method $this getAttribute(string $key, string $value)
 *
 * @package Tale\Dom
 */
class Manipulator implements IteratorAggregate, Countable
{

    /**
     * @var array
     */
    private $_elements;

    /**
     * Manipulator constructor.
     *
     * @param Element[]|Traversable|string $elements
     */
    public function __construct($elements = null)
    {

        $this->_elements = [];

        if ($elements !== null)
            $this->_elements = static::parseElementArray($elements);
    }

    /**
     * @return Element[]
     */
    public function elements()
    {

        return $this->_elements;
    }

    public function first()
    {

        return count($this) < 1 ? null : $this->_elements[0];
    }

    public function last()
    {

        $len = count($this);
        return $len < 1 ? null : $this->_elements[$len - 1];
    }

    /**
     * @param Element[]|Manipulator|string $elements
     *
     * @return static
     * @throws Exception
     */
    public function add($elements)
    {

        $m = clone $this;
        foreach (static::parseElements($elements) as $el)
            $m->_elements[] = $el;

        return $m;
    }

    /**
     * @param string $selectors
     *
     * @return static
     */
    public function query($selectors)
    {

        $m = clone $this;
        $m->_elements = [];
        foreach ($this->_elements as $el)
            foreach ($el->query($selectors) as $foundEl)
                $m->_elements[] = $foundEl;

        return $m;
    }

    /**
     * @param string|int $selector
     *
     * @return static
     */
    public function parent($selector = null)
    {

        if (is_int($selector)) {

            $result = $this;
            while ($selector--)
                $result = $result->parent();

            return $result;
        }

        $m = clone $this;
        $m->_elements = [];
        foreach ($this->_elements as $el) {

            if (!$el->hasParent())
                continue;

            $parent = $el->getParent();
            if (!is_string($selector) || $el->matches($selector))
                $m->_elements[] = $parent;
        }

        return $m;
    }

    /**
     * @param string $selector
     *
     * @return static
     */
    public function parents($selector = null)
    {

        $result = $this;
        $parents = $this;
        while (count($parents = $parents->parent()) > 0) {

            $result = $result->add(!$selector ? $parents : $parents->filter($selector));
        }

        return $result;
    }

    /**
     * @return static
     */
    public function root()
    {

        $current = $this;
        $result = $this;
        while (count($current = $current->parent()))
            $result = $current;

        return $result;
    }

    /**
     * @param string $selector
     *
     * @return bool
     */
    public function is($selector = null)
    {

        foreach ($this->_elements as $el)
            if (!$el->matches($selector))
                return false;

        return true;
    }

    /**
     * @param Element[]|Traversable|string $elements
     *
     * @return static
     * @throws Exception
     */
    public function append($elements)
    {

        $m = new static($elements);
        foreach ($m->elements() as $appendEl)
            foreach ($this->_elements as $el)
                $el->appendChild($appendEl);

        return $m;
    }

    /**
     * @param Element[]|Traversable|string $elements
     *
     * @return static
     */
    public function appendOrAdd($elements)
    {

        return count($this) > 0 ? $this->append($elements) : $this->add($elements);
    }

    /**
     * @param Element[]|Traversable|string $elements
     *
     * @return static
     * @throws Exception
     */
    public function prepend($elements)
    {

        $m = new static($elements);
        foreach ($m->elements() as $prependEl)
            foreach ($this->_elements as $el)
                $el->prependChild($prependEl);

        return $m;
    }

    /**
     * @param Element[]|Traversable|string $elements
     *
     * @return static
     */
    public function prependOrAdd($elements)
    {

        return count($this) > 0 ? $this->prepend($elements) : $this->add($elements);
    }

    /**
     * @param Element[]|Traversable|string $elements
     *
     * @return static
     * @throws Exception
     */
    public function before($elements)
    {

        $m = new static($elements);
        foreach ($m->elements() as $prependEl)
            foreach ($this->_elements as $el)
                $el->prepend($prependEl);

        return $m;
    }

    /**
     * @param Element[]|Traversable|string $elements
     *
     * @return static
     * @throws Exception
     */
    public function after($elements)
    {

        $m = new static($elements);
        foreach ($m->elements() as $prependEl)
            foreach ($this->_elements as $el)
                $el->append($prependEl);

        return $m;
    }

    /**
     * @param Element[]|Traversable|string $elements
     *
     * @return static
     */
    public function appendTo($elements)
    {

        $this->append($elements);
        return $this;
    }

    /**
     * @param Element[]|Traversable|string $elements
     *
     * @return static
     */
    public function prependTo($elements)
    {

        $this->prepend($elements);
        return $this;
    }

    /**
     * @param Selector|string $selector
     *
     * @return static
     * @throws Exception
     */
    public function filter($selector)
    {

        $selector = $selector instanceof Selector || is_callable($selector) ? $selector : Selector::fromString($selector);
        $filter = is_callable($selector) ? $selector : function (ElementInterface $el) use ($selector) {

            return $el->matches($selector);
        };

        return new static(array_filter($this->_elements, $filter));
    }

    /**
     * @param callable $handler
     *
     * @return mixed[]|static
     */
    public function map(callable $handler)
    {

        $result = array_map($handler, $this->_elements, array_keys($this->_elements));

        foreach ($result as $item)
            if (!($item instanceof ElementInterface))
                return $result;

        return new static($result);
    }

    /**
     * @return static
     */
    public function clear()
    {

        foreach ($this->_elements as $el)
            $el->removeChildren();

        return $this;
    }

    public function getText()
    {

        return implode('', array_map(function(ElementInterface $el) {

            return $el->getText();
        }, $this->_elements));
    }

    /**
     * @return int
     */
    public function count()
    {

        return count($this->_elements);
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {

        foreach ($this->_elements as $el)
            yield new static($el);
    }

    /**
     * @param Formatter $format
     *
     * @return string
     */
    public function getString(Formatter $format = null, $separator = null)
    {

        return implode($separator ?: '', $this->map(function(ElementInterface $e) use ($format) {

            return $e->format($format);
        }));
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getString();
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return static|mixed[]
     */
    public function __call($method, array $args)
    {

        //Direct proxy on this instance
        //We need this for __get calls that proxy without ()
        //and won't trigger __call (since they actually exist)
        if (method_exists($this, $method)) {

            //Just proxy dat shit
            return call_user_func_array([$this, $method], $args);
        }

        //Automatic element access/creation
        if (!method_exists(static::getElementClassName(), $method)) {

            $selector = $method.(count($args) > 0 ? (string)$args[0] : '');
            $attributes = count($args) > 1 ? $args[1] : [];

            if (!is_array($attributes))
                throw new \InvalidArgumentException(
                    "Argument 2 passed to Manipulator->__call needs to be ".
                    "attribute array when creating elements"
                );

            //First we check if there's a direct descendant of this selector already
            $els = $this->query(">$selector");
            if (count($els))
                $m = $els;
            else
                $m = $this->appendOrAdd($selector);

            if (count($attributes))
                foreach ($attributes as $key => $value)
                    $m->setAttribute($key, $value);

            return $m;
        }

        //In case you want to act on the elements ($m->getText(), $m->setCss() etc.)
        $result = [];
        foreach ($this->_elements as $el)
            $result[] = call_user_func_array([$el, $method], $args);

        foreach ($result as $item)
            if (!is_a($item, static::getElementClassName()))
                return $result;

        return new static($result);
    }

    /**
     * @param $method
     *
     * @return static|array
     */
    public function __get($method)
    {

        return $this->__call($method, []);
    }

    /**
     * @param $selector
     *
     * @return static
     */
    public function __invoke($selector)
    {

        return $this->query($selector);
    }

    /**
     * @return string
     */
    public static function getElementClassName()
    {

        return Element::class;
    }

    /**
     * @param Element|string $element
     *
     * @return Element
     * @throws Exception
     */
    public static function parseElement($element)
    {

        if ($element instanceof Element)
            return $element;

        $elementClass = static::getElementClassName();
        if (is_string($element) && strlen($element) > 1 && $element[0] === '<')
            return call_user_func([$elementClass, 'fromString'], $element);

        return call_user_func(
            [$elementClass, 'fromSelector'],
            $element instanceof Selector ? $element : (string)$element
        );
    }

    /**
     * @param Element[]|array|string $elements
     *
     * @return \Generator
     * @throws Exception
     */
    public static function parseElements($elements)
    {

        if (is_array($elements) || $elements instanceof Traversable) {

            foreach ($elements as $index => $element) {

                if (is_string($index)) {

                    $parent = static::parseElement($index);

                    if (is_string($element))
                        $parent->setText($element);
                    else
                        $parent->setChildren(static::parseElementArray($element));

                    yield $parent;
                    continue;
                }

                if (is_array($element)) {

                    foreach (static::parseElements($element) as $el)
                        yield $el;
                    continue;
                }

                yield static::parseElement($element);
            }
            return;
        }

        yield static::parseElement($elements);
    }

    /**
     * @param Element[]|array|string $elements
     *
     * @return Element[]
     * @throws Exception
     */
    public static function parseElementArray($elements)
    {

        return iterator_to_array(static::parseElements($elements));
    }
}