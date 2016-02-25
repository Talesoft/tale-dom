<?php

namespace Tale\Dom\Html;

use Tale\Dom\Element as DomElement;

class Element extends DomElement
{

    private static $_selfClosingTags = [
        'input', 'br', 'img', 'link',
        'area', 'base', 'col', 'command',
        'embed', 'hr', 'keygen', 'meta',
        'param', 'source', 'track', 'wbr'
    ];

    public function hasCss()
    {

        return $this->hasAttribute('style');
    }

    public function getCss()
    {

        if (!$this->hasAttribute('style'))
            return [];

        $result = [];
        $parts = array_map('trim', explode(';', $this->getAttribute('style')));
        foreach ($parts as $part) {

            $part = trim($part);

            if (empty($part))
                continue;

            list($property, $value) = explode(':', $part);
            $result[trim($property)] = trim($value);
        }

        return $result;
    }

    public function setCss(array $css, $merge = true)
    {

        if ($merge)
            $css = array_replace($this->getCss(), $css);

        $this->setAttribute('style', implode(' ', array_map(function ($property, $value) {

            return "$property: $value;";
        }, array_keys($css), $css)));

        return $this;
    }

    public function getString($pretty = false, $requireCloseTag = false, $selfClosingTags = null, $level = null)
    {
        return parent::getString($pretty, $requireCloseTag, array_merge(self::$_selfClosingTags, $selfClosingTags ?: []), $level);
    }

    public static function getParserClassName()
    {

        return Parser::class;
    }
}