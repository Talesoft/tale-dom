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

    static $registeredPseudos = [
        'first-child' => [Pseudo::class, 'isFirstChild'],
        'last-child'  => [Pseudo::class, 'isLastChild'],
        'nth-child'   => [Pseudo::class, 'isNthChild'],
        'not'         => [Pseudo::class, 'isNot'],
        'even'        => [Pseudo::class, 'isEven'],
        'odd'         => [Pseudo::class, 'isOdd']
    ];

    private $name;
    private $id;
    private $classes;
    private $attributes;
    private $pseudos;

    public function __construct($name = null, $id = null, array $classes = null, array $attributes = null, array $pseudos = null)
    {

        $this->name = $name;
        $this->id = $id;
        $this->classes = $classes ?: [];
        $this->attributes = $attributes ?: [];
        $this->pseudos = $pseudos ?: [];
    }

    public function getName()
    {

        return $this->name;
    }

    public function getId()
    {

        return $this->id;
    }

    public function getClasses()
    {

        return $this->classes;
    }

    public function getAttributes()
    {

        return $this->attributes;
    }

    public function getPseudos()
    {

        return $this->pseudos;
    }

    public function matches(Element $element)
    {

        if ($this->name && $this->name !== '*' && $element->getName() !== $this->name)
            return false;

        if ($this->id && (!$element->hasId() || $element->getId() !== $this->id))
            return false;

        if (!empty($this->classes)) {

            foreach ($this->classes as $class)
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

        if (!empty($this->pseudos)) {

            foreach ($this->pseudos as $name => $value) {

                if (!array_key_exists($name, self::$registeredPseudos))
                    throw new Exception(
                        "Failed to resolve selector: Pseudo $name doesnt exist"
                    );

                $idx = $element->getIndex();
                if (!call_user_func(self::$registeredPseudos[$name], $value, $element, $idx))
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

        self::$registeredPseudos[$name] = $callback;
    }

    public static function removePseudo($name)
    {

        unset(self::$registeredPseudos[$name]);
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