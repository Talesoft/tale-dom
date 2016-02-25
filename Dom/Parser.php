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

    /**
     * @var string
     */
    private $_encoding;

    /**
     * @var resource
     */
    private $_internalParser;
    /**
     * @var Element
     */
    private $_currentElement;

    /**
     * Parser constructor.
     *
     * @param string $encoding
     */
    public function __construct($encoding = null)
    {

        $this->_encoding = $encoding;
    }

    /**
     * @param string $string
     *
     * @return Element
     * @throws Exception
     */
    public function parse($string)
    {

        $this->_internalParser = xml_parser_create($this->_encoding);

        xml_set_object($this->_internalParser, $this);

        xml_parser_set_option($this->_internalParser, \XML_OPTION_CASE_FOLDING, false);
        xml_parser_set_option($this->_internalParser, \XML_OPTION_SKIP_WHITE, true);

        xml_set_element_handler($this->_internalParser, 'handleOpenTag', 'handleCloseTag');
        xml_set_character_data_handler($this->_internalParser, 'handleText');

        $this->_currentElement = null;
        if (!xml_parse($this->_internalParser, $string))
            $this->throwException();

        if (is_resource($this->_internalParser))
            xml_parser_free($this->_internalParser);

        return $this->_currentElement;
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

        $className = static::getElementClassName();
        $this->_currentElement = new $className($tag, $attrs, $this->_currentElement);
    }

    /**
     * @param resource $parser
     * @param string $tag
     *
     * @throws Exception
     */
    protected function handleCloseTag($parser, $tag)
    {

        $cur = $this->_currentElement;
        $className = static::getElementClassName();
        if (!$cur || !is_a($cur, $className) || $cur->getName() !== $tag)
            $this->throwException(
                "Close-tag mismatch for tag $tag"
            );

        if ($cur->hasParent())
            $this->_currentElement = $cur->getParent();
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

        if (!$this->_currentElement)
            $this->throwException(
                "Unexpected text, expected element"
            );

        $textClass = call_user_func([static::getElementClassName(), 'getTextClassName']);
        $this->_currentElement->appendChild(new $textClass($text));
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
                $message ? $message : xml_error_string(xml_get_error_code($this->_internalParser)),
                xml_get_current_line_number($this->_internalParser),
                xml_get_current_column_number($this->_internalParser)
            )
        );
    }

    /**
     * @return string
     */
    public static function getElementClassName()
    {

        return Element::class;
    }
}