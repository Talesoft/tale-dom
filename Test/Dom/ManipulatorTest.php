<?php

namespace Tale\Test\Dom;

use Tale\Dom;

class ManipulatorTest extends \PHPUnit_Framework_TestCase
{

    public function testPragmatic1()
    {

        $m = Dom::manipulate(
            '<html><head><title /><link /></head><body><p>Some <strong>text</strong> fucking awesome text</p></body></html>',
            true
        );

        $markup = (string)$m
            ->body
            ->append('h1.title#mainTitle')
                ->setText('Welcome to Tale DOM!')
            ->after('p.first')
                ->setText('This shit is fucking awesome!')
            ->after('p.second')
                ->setText(
                    'This is a longer fucking text. This text has a shitload of characters that exceed the internal line-limit completely.'
                )
            ->after('p.third')
                ->setText(
                    'This is a longer fucking text. This text has a shitload of characters that exceed the internal line-limit completely.'
                )
            ->root;

        $this->assertEquals('<html><head><title></title><link></head><body><p>Some<strong>text</strong>fucking awesome text</p><h1 id="mainTitle" class="title">Welcome to Tale DOM!</h1><p class="first">This shit is fucking awesome!</p><p class="second">This is a longer fucking text. This text has a shitload of characters that exceed the internal line-limit completely.</p><p class="third">This is a longer fucking text. This text has a shitload of characters that exceed the internal line-limit completely.</p></body></html>', $markup);
    }

    public function testPragmatic2()
    {

        $m = Dom::manipulate('
            <config>
                <db>
                    <host />
                    <password />
                </db>
                <logging>
                    <adapter />
                    <path id="logPath" />
                </logging>
            </config>
        ');

        $m->query('host')->setText('localhost');
        $m->query('db > password')->setText('12345');
        $m->query('logging adapter')->setText('file');
        $m->query('#logPath')->setText('./errors.log');

        $this->assertEquals('<config><db><host>localhost</host><password>12345</password></db><logging><adapter>file</adapter><path id="logPath">./errors.log</path></logging></config>', (string)$m);
    }

    public function testArrayDom()
    {

        $m = Dom::manipulate([
            'html[lang="en"]' => [
                'head' => [
                    'meta[charset="utf-8"]',
                    'title' => 'My awesome array website :)',
                    'link[rel="stylesheet"][href="common.css"]'
                ],
                'body' => [
                    'h1' => 'Welcome to Tale DOM!',
                    'p.first' => 'This shit is really fucking awesome.',
                    'p.second' => 'I\'m awesome!',
                    'p.third' => 'You\'re awesome!',
                    'table' => [
                        'tr.tr-1' => [
                            'th.th-1' => 'Col 1',
                            'th.th-2' => 'Col 2',
                            'th.th-3' => 'Col 3'
                        ],
                        'tr.tr-2' => [
                            'td.td-1' => 'Cell 1',
                            'td.td-2' => 'Cell 2',
                            'td.td-3' => 'Cell 3'
                        ],
                        'tr.tr-3' => [
                            'td.td-1' => 'Cell 4',
                            'td.td-2' => 'Cell 5',
                            'td.td-3' => 'Cell 6'
                        ]
                    ]
                ]
            ]
        ], true);

        $this->assertEquals('<html lang="en"><head><meta charset="utf-8"><title>My awesome array website :)</title><link rel="stylesheet" href="common.css"></head><body><h1>Welcome to Tale DOM!</h1><p class="first">This shit is really fucking awesome.</p><p class="second">I\'m awesome!</p><p class="third">You\'re awesome!</p><table><tr class="tr-1"><th class="th-1">Col 1</th><th class="th-2">Col 2</th><th class="th-3">Col 3</th></tr><tr class="tr-2"><td class="td-1">Cell 1</td><td class="td-2">Cell 2</td><td class="td-3">Cell 3</td></tr><tr class="tr-3"><td class="td-1">Cell 4</td><td class="td-2">Cell 5</td><td class="td-3">Cell 6</td></tr></table></body></html>', (string)$m);
    }
}