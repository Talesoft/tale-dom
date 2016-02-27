<?php

namespace Tale\Dom;

interface ElementInterface extends NodeInterface
{

    public function getName();
    public function setName($name);
    public function getClasses();
    public function setClasses($classes);
    public function getId();
    public function setId($id);
    public function getAttributes();
    public function setAttributes(array $attributes);

    public function getText();
    public function setText($text);
}