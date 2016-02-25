<?php

namespace Tale\Test\Dom;

use Tale\Dom\Element;
use Tale\Dom\Html\Element as HtmlElement;
use Tale\Dom\Parser;
use Tale\Dom\Html\Parser as HtmlParser;
use Tale\Dom\Text;


class ParserTest extends \PHPUnit_Framework_TestCase
{

    public function testParseElement()
    {

        $parser = new Parser();
        $element = $parser->parse('<some-element></some-element>');
        $this->assertInstanceOf(Element::class, $element);
        $this->assertEquals('some-element', $element->getName());
    }

    public function testParseHtmlElement()
    {

        $parser = new HtmlParser();
        $element = $parser->parse('<some-element></some-element>');
        $this->assertInstanceOf(HtmlElement::class, $element);
        $this->assertEquals('some-element', $element->getName());
    }

    public function testParseText()
    {

        $parser = new Parser();
        /** @var Element $element */
        $element = $parser->parse('<some-element>Some text</some-element>');
        $this->assertInstanceOf(Element::class, $element);
        $this->assertEquals(1, count($element));
        /** @var Text $textChild */
        $textChild = $element->getChildAt(0);
        $this->assertInstanceOf(Text::class, $textChild);
        $this->assertEquals('Some text', $element->getText(), 'element text');
        $this->assertEquals('Some text', $textChild->getText(), 'text child text');
    }
}