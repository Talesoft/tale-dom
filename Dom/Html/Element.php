<?php

namespace Tale\Dom\Html;

use Tale\Dom\Element as DomElement;
use Tale\Dom\Formatter as DomFormatter;

class Element extends DomElement
{

    const STYLE_ATTRIBUTE = 'style';

    public function hasCss()
    {

        return $this->hasAttribute(static::STYLE_ATTRIBUTE);
    }

    public function getCss()
    {

        if (!$this->hasCss())
            return [];

        $result = [];
        $parts = array_map('trim', explode(';', $this->getAttribute(static::STYLE_ATTRIBUTE)));
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

        $this->setAttribute(static::STYLE_ATTRIBUTE, implode(' ', array_map(function ($property, $value) {

            return "$property: $value;";
        }, array_keys($css), $css)));

        return $this;
    }

    public function format(DomFormatter $formatter = null, $level = null)
    {

        $formatter = $formatter ? $formatter : new Formatter();
        return parent::format($formatter, $level);
    }

    public static function getParserClassName()
    {

        return Parser::class;
    }
}