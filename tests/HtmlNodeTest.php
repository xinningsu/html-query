<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\{HQ, HtmlElement};

class HtmlNodeTest extends TestCase
{
    public function testReplaceWith()
    {
        $html = '
            <ul class="ul">
                <li class="php">PHP</li>
                <li class="dom">Dom</li>
                <li class="js">JS</li>
            </ul>
        ';
        $hq = HQ::html($html);
        $php = $hq('.php')[0];
        $dom = $hq('.dom')[0];
        $js = $hq('.js')[0];

        $hn = new HtmlElement($php);
        $hn->replaceWith($dom);

        $this->assertHtmlEquals(
            '
            <ul class="ul">
                <li class="dom">Dom</li>
                <li class="js">JS</li>
            </ul>',
            $hq->find('.ul')->outerHtml()
        );
    }

    public function testClosureResolve()
    {
        $html = '
            <ul class="ul">
                <li class="php">PHP</li>
                <li class="dom">Dom</li>
                <li class="js">JS</li>
            </ul>
        ';

        $hq = HQ::html($html);
        $ht = $hq('<div><p>html query</p></div>');
        $ht->setAttr('name', 'test');
        $this->assertHtmlEquals(
            '<div><p>html query</p></div>',
            $ht->outerHtml()
        );

        $ht->setAttr('name', 'test');
        $this->assertEquals(
            [null],
            $ht->map(function (HtmlElement $node) {
                return $node->getAttr('test');
            })
        );
    }
}
