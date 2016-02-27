<?php

namespace Tale\Dom;

interface TextInterface extends LeafInterface
{

    public function getText();
    public function setText($text);
}