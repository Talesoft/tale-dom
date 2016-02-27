<?php

namespace Tale\Dom\Html;

use Tale\Dom\Formatter as DomFormatter;

class Formatter extends DomFormatter
{

    private static $_selfClosingElements = [
        'input', 'br', 'img', 'link',
        'area', 'base', 'col', 'command',
        'embed', 'hr', 'keygen', 'meta',
        'param', 'source', 'track', 'wbr'
    ];

    public function __construct(array $options = null)
    {

        parent::__construct([
            'allowSelfClosing' => false,
            'selfClosingElements' => self::$_selfClosingElements,
            'selfClosingStyle' => ''
        ]);

        if ($options)
            $this->setOptions($options);
    }
}