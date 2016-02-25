<?php

namespace Tale\Test\Dom;

use Tale\Dom\Element;
use Tale\Dom\Html\Element as HtmlElement;
use Tale\Dom\Html\Manipulator;
use Tale\Dom\Parser;
use Tale\Dom\Html\Parser as HtmlParser;
use Tale\Dom\Text;


class ManipulatorTest extends \PHPUnit_Framework_TestCase
{

    public function testPragmatic()
    {

        $m = new Manipulator('<html><head><title /><link /></head><body><p>Some <strong>text</strong> fucking awesome text</p></body></html>');
        $markup = (string)$m
            ->body
                ->h1
                    ->setText('Welcome to Tale DOM!')
                    ->parent
                ->{'p:nth-child(2)'}
                    ->setText('This shit is fucking awesome!')
                    ->parent
                ->{'p:nth-child(3)'}
                    ->setText(
                        'This is a longer fucking text. This text has a shitload of characters that exceed the internal line-limit completely.'
                    )
                    ->parent
                ->{'p:nth-child(4)'}
                    ->setText(
                        'This is a longer fucking text. This text has a shitload of characters that exceed the internal line-limit completely.'
                    )
                    ->root;

        $this->assertEquals('<html><head><title /><link></head><body><p>Some</p>fucking awesome text</p><strong>text</strong></p><h1>Welcome to Tale DOM!</h1><p>This is a longer fucking text. This text has a shitload of characters that exceed the internal line-limit completely.</p></p><p>This is a longer fucking text. This text has a shitload of characters that exceed the internal line-limit completely.</p></p></body></html>', $markup);
    }
}