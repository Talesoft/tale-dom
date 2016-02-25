<?php

namespace Tale\Dom;

use Exception;

class Selector
{

    const EXPRESSION = '
    /
        (?<tag>[a-z][a-z0-9_\-\*\|]*)?           #tag
        (?:\.                                  #classes
            (?<class>[a-z][a-z0-9_\-]*)
        )?
        (?:\#(?<id>[a-z][a-z0-9_\-]*))?        #id
        (?:\[                                  #attributes
            (?<attr>[a-z][a-z0-9_\-:~\*\^\$!]*)
            (?:=(?<attrValue>[^\]]*))?
        \])?
        (?:\:                                  #pseudos
            (?<pseudo>[a-z][a-z0-9_\-]*)
            (?:\((?<pseudoValue>[^\)]*)\))?
        )?
    /ix';

    static $_registeredPseudos = [
        'first-child' => [Pseudo::class, 'isFirstChild'],
        'last-child'  => [Pseudo::class, 'isLastChild'],
        'nth-child'   => [Pseudo::class, 'isNthChild'],
        'not'         => [Pseudo::class, 'isNot'],
        'even'        => [Pseudo::class, 'isEven'],
        'odd'         => [Pseudo::class, 'isOdd']
    ];

    private $_name;
    private $_id;
    private $_classes;
    private $_attributes;
    private $_pseudos;

    public function __construct($name = null, $id = null, array $classes = null, array $attributes = null, array $pseudos = null)
    {

        $this->_name = $name;
        $this->_id = $id;
        $this->_classes = $classes ? $classes : [];
        $this->_attributes = $attributes ? $attributes : [];
        $this->_pseudos = $pseudos ? $pseudos : [];
    }

    public function getName()
    {

        return $this->_name;
    }

    public function getId()
    {

        return $this->_id;
    }

    public function getClasses()
    {

        return $this->_classes;
    }

    public function getAttributes()
    {

        return $this->_attributes;
    }

    public function getPseudos()
    {

        return $this->_pseudos;
    }

    public function matches(Element $element)
    {

        if ($this->_name && $this->_name !== '*' && $element->getName() !== $this->_name)
            return false;

        if ($this->_id && (!$element->hasId() || $element->getId() !== $this->_id))
            return false;

        if (!empty($this->_classes)) {

            foreach ($this->_classes as $class)
                if (!$element->hasClass($class))
                    return false;
        }

        if (!empty($attributes)) {

            foreach ($attributes as $name => $value) {

                if (!$element->hasAttribute($name))
                    return false;

                if ($value !== null && $element->getAttribute($name) !== $value)
                    return false;
            }
        }

        if (!empty($this->_pseudos)) {

            foreach ($this->_pseudos as $name => $value) {

                if (!array_key_exists($name, self::$_registeredPseudos))
                    throw new Exception("Failed to resolve selector: Pseudo $name doesnt exist");

                $idx = $element->getIndex();
                if (!call_user_func(self::$_registeredPseudos[$name], $value, $element, $idx))
                    return false;
            }
        }

        return true;
    }

    public static function registerPseudo($name, $callback)
    {

        if (!is_callable($callback))
            throw new \InvalidArgumentException(
                "Argument 2 passed to Selector::registerPseudo needs to be ".
                "valid callback"
            );

        self::$_registeredPseudos[$name] = $callback;
    }

    public static function removePseudo($name)
    {

        unset(self::$_registeredPseudos[$name]);
    }

    public static function isValid($selectorString)
    {

        return preg_match(self::EXPRESSION, $selectorString);
    }

    public static function fromString($selectorString)
    {

        $matches = [];
        $success = preg_match_all(self::EXPRESSION, $selectorString, $matches);

        if (!$success)
            throw new Exception(
                "Failed to parse selector: Passed string is not a valid selector"
            );

        $tag = null;
        foreach ($matches['tag'] as $matchTag)
            if (!empty($matchTag))
                $tag = str_replace('|', ':', $matchTag);

        $classes = [];
        foreach ($matches['class'] as $class)
            if (!empty($class))
                $classes[] = $class;

        $id = null;
        foreach ($matches['id'] as $matchId)
            if (!empty($matchId))
                $id = $matchId;

        $attrs = [];
        foreach ($matches['attr'] as $i => $name) {

            if (empty($name))
                continue;

            $value = null;

            if (!empty($matches['attrValue'][$i]))
                $value = trim($matches['attrValue'][$i], '"\'');

            $attrs[$name] = $value;
        }

        $pseudos = [];
        foreach ($matches['pseudo'] as $i => $name) {

            if (empty($name))
                continue;

            $value = null;

            if (!empty($matches['pseudoValue'][$i]))
                $value = trim($matches['pseudoValue'][$i], '"\'');

            $pseudos[$name] = $value;
        }

        return new static($tag, $id, $classes, $attrs, $pseudos);
    }
}