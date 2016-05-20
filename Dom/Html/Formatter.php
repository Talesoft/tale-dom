<?php

namespace Tale\Dom\Html;

use Tale\Dom\Formatter as DomFormatter;

class Formatter extends DomFormatter
{

    private static $selfClosingElements = [
        'input', 'br', 'img', 'link',
        'area', 'base', 'col', 'command',
        'embed', 'hr', 'keygen', 'meta',
        'param', 'source', 'track', 'wbr'
    ];

    public function __construct(array $options = null)
    {

        parent::__construct([
            'allow_self_closing' => false,
            'self_closing_elements' => self::$selfClosingElements,
            'self_closing_style' => ''
        ]);

        if ($options)
            $this->setOptions($options);
    }
}