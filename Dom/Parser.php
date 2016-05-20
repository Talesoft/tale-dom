<?php

namespace Tale\Dom;

use Exception;

/**
 * Class Parser
 *
 * @package Tale\Dom
 */
class Parser
{

    /**
     *
     */
    const DEFAULT_ENCODING = 'utf-8';

    protected static $elementClassName = Element::class;
    protected static $textClassName = Text::class;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @var resource
     */
    private $internalParser;
    /**
     * @var Element
     */
    private $currentElement;

    /**
     * Parser constructor.
     *
     * @param string $encoding
     */
    public function __construct($encoding = null)
    {

        $this->encoding = $encoding;
    }

    /**
     * @param string $string
     *
     * @return Element
     * @throws Exception
     */
    public function parse($string)
    {

        $this->internalParser = xml_parser_create($this->encoding);

        xml_set_object($this->internalParser, $this);

        xml_parser_set_option($this->internalParser, \XML_OPTION_CASE_FOLDING, false);
        xml_parser_set_option($this->internalParser, \XML_OPTION_SKIP_WHITE, true);

        xml_set_element_handler($this->internalParser, 'handleOpenTag', 'handleCloseTag');
        xml_set_character_data_handler($this->internalParser, 'handleText');

        $string = Parser::normalize($string);

        $this->currentElement = null;
        if (!xml_parse($this->internalParser, Parser::normalize($string)))
            $this->throwException();

        if (is_resource($this->internalParser))
            xml_parser_free($this->internalParser);

        return $this->currentElement;
    }

    public function parseFile($path)
    {

        return $this->parse(file_get_contents($path));
    }

    /**
     * @param resource $parser
     * @param string $tag
     * @param array $attrs
     */
    protected function handleOpenTag($parser, $tag, array $attrs)
    {

        $className = static::$elementClassName;
        $this->currentElement = new $className($tag, $attrs, $this->currentElement);
    }

    /**
     * @param resource $parser
     * @param string $tag
     *
     * @throws Exception
     */
    protected function handleCloseTag($parser, $tag)
    {

        $cur = $this->currentElement;
        $className = static::$elementClassName;
        if (!$cur || !is_a($cur, $className) || $cur->getName() !== $tag)
            $this->throwException(
                "Close-tag mismatch for tag $tag"
            );

        if ($cur->hasParent())
            $this->currentElement = $cur->getParent();
    }

    /**
     * @param resource $parser
     * @param string $text
     */
    protected function handleText($parser, $text)
    {

        $text = trim($text);

        if (empty($text))
            return;

        if (!$this->currentElement)
            $this->throwException(
                "Unexpected text, expected element"
            );

        $textClass = static::$textClassName;
        $this->currentElement->appendChild(new $textClass($text));
    }

    public static function normalize($string)
    {

        return preg_replace('/[\r\n\t]|[ ]{2,}/', ' ', $string);
    }

    /**
     * @param string $message
     *
     * @throws Exception
     */
    protected function throwException($message = null)
    {

        throw new Exception(
            sprintf(
                'Failed to parse DOM: %s on at %d:%d',
                $message ?: xml_error_string(xml_get_error_code($this->internalParser)),
                xml_get_current_line_number($this->internalParser),
                xml_get_current_column_number($this->internalParser)
            )
        );
    }
}