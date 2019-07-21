<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\{Helper, HQ, HtmlQuery};

class ResolverTest extends TestCase
{
    public function testResolve()
    {
        $html = '
            <div id="foo"><p>foo</p></div>
            <div id="bar">bar</div>
            <div class="fruit bar">apple</div>
            <div class="fruit">orange</div>
            <div class="fruit">banana</div>
        ';

        $hq = HQ::html($html);
        $nodes = $this->protectMethod($hq, 'xpathQuery')(
            Helper::toXpath('.fruit')
        );
        $instance = $this->protectMethod($hq, 'resolve')($nodes);

        $this->assertTrue($instance instanceof HtmlQuery);
        $this->assertEquals($nodes, $instance->toArray());

        $this->assertEquals('apple', $instance->html());
    }

    public function testXpathFind()
    {
        $html = '
            <div id="foo"><p>foo</p></div>
            <div id="bar">bar</div>
            <div class="fruit bar">apple</div>
            <div class="fruit">orange</div>
            <div class="fruit">banana</div>
        ';
        $doc = new DOMDocument();
        $doc->loadHTML($html);

        $hq = HQ::html($html);

        $xpathFind = $this->protectMethod($hq, 'xpathFind');

        $xpath = "descendant::*[@class and contains(concat(' ', "
            . "normalize-space(@class), ' '), ' fruit ')]";

        $this->assertEquals(
            $this->protectMethod($hq, 'xpathQuery')($xpath),
            $xpathFind($xpath)->toArray()
        );
    }

    public function testXpathQuery()
    {
        $html = '
            <div id="foo"><p>foo</p></div>
            <div id="bar">bar</div>
            <div class="fruit">apple</div>
            <div class="fruit">orange</div>
            <div class="fruit">banana</div>
        ';
        $doc = new DOMDocument();
        $doc->loadHTML($html);

        $hq = new HtmlQuery($doc, []);

        $nodes = $this->protectMethod($hq, 'xpathQuery')(
            Helper::toXpath('#foo')
        );
        $this->assertEquals(1, count($nodes));
        $this->assertEquals(
            '<div id="foo"><p>foo</p></div>',
            $doc->saveHTML($nodes[0])
        );

        $nodes = $this->protectMethod($hq, 'xpathQuery')(
            Helper::toXpath('#foobar')
        );
        $this->assertEquals([], $nodes);

        $this->assertEquals(
            [],
            $this->protectMethod($hq, 'xpathQuery')('^&9*')
        );

        $nodes = $this->protectMethod($hq, 'xpathQuery')(
            Helper::toXpath('#foo p')
        );
        $this->assertEquals(1, count($nodes));

        $nodes = $this->protectMethod($hq, 'xpathQuery')(
            Helper::toXpath('.fruit')
        );
        $this->assertEquals(3, count($nodes));
        $this->assertEquals(
            '<div class="fruit">banana</div>',
            $doc->saveHTML($nodes[2])
        );
    }

    public function testTargetResolve()
    {
        $html = '
            <div id="foo"><p>foo</p></div>
            <div id="bar">bar</div>
            <div class="fruit bar">apple</div>
            <div class="fruit">orange</div>
            <div class="fruit">banana</div>
        ';
        $hq = HQ::html($html);

        $nodes = $this->protectMethod($hq, 'xpathQuery')(
            Helper::toXpath('.bar')
        );
        $this->assertEquals(
            $nodes,
            ($this->protectMethod($hq, 'targetResolve')('.bar'))->toArray()
        );

        $this->assertEquals(
            'apple',
            ($this->protectMethod($hq, 'targetResolve')('.bar'))->html()
        );
    }

    public function testContentResolve()
    {
        $html = '
            <div class="container">
                <div id="foo"><p>foo</p></div>
                <div id="bar">bar</div>
                <div class="fruit bar">apple</div>
                <div class="fruit">orange</div>
                <div class="fruit">banana</div>
            </div>
        ';
        $hq = HQ::html($html);

        ($this->protectMethod($hq, 'contentResolve')(
            '<div class="fruit">pear</div>'
        ))
            ->appendTo($hq('.container'));
        $this->assertHtmlEquals(
            '
            <div class="container">
                <div id="foo"><p>foo</p></div>
                <div id="bar">bar</div>
                <div class="fruit bar">apple</div>
                <div class="fruit">orange</div>
                <div class="fruit">banana</div>
                <div class="fruit">pear</div>
            </div>
            ',
            $hq->outerHtml()
        );
    }

    public function testRelationResolve()
    {
        $html = '
            <body>
                <div class="container">
                    <ul class="fruit">
                        <li>apple</li>
                        <li>orange</li>
                        <li>banana</li>
                    </ul>
                </div>
            </body>
        ';
        $hq = HQ::html($html);

        $li = $hq->find('li')->eq(0);

        $this->assertEquals(
            3,
            ($this->protectMethod($li, 'relationResolve')('parentNode'))
                ->count()
        );
        $this->assertEquals(
            0,
            ($this->protectMethod($li, 'relationResolve')(
                'parentNode',
                '.fruit'
            ))->count()
        );
        $this->assertEquals(
            1,
            ($this->protectMethod($li, 'relationResolve')(
                'parentNode',
                '.container'
            ))->count()
        );
        $this->assertEquals(
            2,
            ($this->protectMethod($li, 'relationResolve')(
                'parentNode',
                'body'
            ))->count()
        );
    }

    public function testHtmlResolve()
    {
        $html = '
            <div id="foo"><p>foo</p></div>
            <div id="bar">bar</div>
            <div class="fruit">apple</div>
            <div class="fruit">orange</div>
            <div class="fruit">banana</div>
        ';
        $doc = new DOMDocument();
        $hq = new HtmlQuery($doc, []);

        $this->assertHtmlEquals(
            $html,
            ($this->protectMethod($hq, 'htmlResolve')($html))->outerHtml()
        );
    }

    public function testShouldResolve()
    {
        $doc = new DOMDocument();
        $hq = new HtmlQuery($doc, []);
        $shouldResolve = $this->protectMethod($hq, 'shouldResolve');

        $this->assertTrue($shouldResolve(function (HtmlQuery $hq) {
        }, 0));
        $this->assertTrue($shouldResolve(function ($index, HtmlQuery $hq) {
        }, 1));

        $this->assertFalse($shouldResolve(function ($hq) {
        }, 0));
        $this->assertFalse($shouldResolve(function ($index, DOMNode $hq) {
        }, 1));
    }
}
