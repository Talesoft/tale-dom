<?php

namespace Tale\Dom;

use Tale\ConfigurableInterface;
use Tale\ConfigurableTrait;

class Formatter implements ConfigurableInterface
{
    use ConfigurableTrait;

    public function __construct(array $options = null)
    {

        $this->defineOptions([
            'pretty' => false,
            'indent_width' => 2,
            'indent_style' => ' ',
            'new_line' => "\n",
            'line_limit' => 60,
            'quote_style' => '"',
            'allow_self_closing' => true,
            'self_closing_elements' => [],
            'self_closing_style' => ' /'
        ], $options);
    }

    public function isPretty()
    {

        return $this->options['pretty'];
    }

    public function getIndentation($level = null)
    {

        return $this->isPretty() ? str_repeat(
            $this->options['indent_style'],
            $this->options['indent_width'] * ($level ?: 0)
        ) : '';
    }

    public function getNewLine()
    {

        return $this->isPretty() ? $this->options['new_line'] : '';
    }

    public function isShortText($string)
    {

        return $this->strlen($string) < $this->options['line_limit'];
    }

    public function isShortTextElement(ElementInterface $element)
    {

        return count($element) === 1 && $element[0] instanceof Text && $this->isShortText($element[0]->getText());
    }

    public function formatText($text, $level = null)
    {

        $indent = $this->getIndentation($level ?: 0);
        $newLine = $this->getNewLine();
        $text = Parser::normalize($text);

        if ($this->isPretty() && !$this->isShortText($text))
            $text = wordwrap(
                $text,
                $this->options['line_limit'],
                "$newLine$indent"
            );

        return $indent.$text;
    }

    public function formatAttributes(array $attributes)
    {

        if (!count($attributes))
            return '';

        $quoteStyle = $this->options['quote_style'];
        return implode(' ', array_map(function($key, $value) use ($quoteStyle) {

            return implode('', [
                $key, '=', $quoteStyle, $value, $quoteStyle
            ]);
        }, array_keys($attributes), $attributes));
    }

    public function isSelfClosing(ElementInterface $element)
    {

        if (count($element) > 0)
            return false;

        return $this->options['allow_self_closing'] || in_array(
            $element->getName(),
            $this->options['self_closing_elements'],
            true
        );
    }

    public function formatElementStart(ElementInterface $element)
    {

        $name = $element->getName();
        $str = "<$name";
        $attrs = $this->formatAttributes($element->getAttributes());
        if (!empty($attrs))
            $str .= " $attrs";

        if ($this->isSelfClosing($element))
            $str .= $this->options['self_closing_style'];

        return "$str>";
    }

    public function formatElementEnd(ElementInterface $element)
    {

        return '</'.$element->getName().'>';
    }

    public function formatElementChildren(ElementInterface $element, $level = null)
    {

        $level = $level ?: 0;
        $count = count($element);

        if ($count < 1)
            return '';

        //Make sure short texts don't create whole new lines (<strong>text</strong>)
        if ($this->isShortTextElement($element))
            return $element[0]->format($this, 0);

        $newLine = $this->getNewLine();
        $str = $newLine;
        foreach ($element as $child)
            $str .= $child->format($this, $level + 1).$newLine;

        return $str;
    }

    public function formatElement(ElementInterface $element, $level = null)
    {

        $level = $level ?: 0;
        $indent = $this->getIndentation($level);
        $str = $indent.$this->formatElementStart($element);

        if (count($element)) {

            $str .= $this->formatElementChildren($element, $level + 1);

            if (!$this->isShortTextElement($element))
                $str .= $indent;
        }

        if (!$this->isSelfClosing($element))
            $str .= $this->formatElementEnd($element);

        return $str;
    }

    private function strlen($string)
    {

        return function_exists('mb_strlen') ? mb_strlen($string) : strlen($string);
    }
}