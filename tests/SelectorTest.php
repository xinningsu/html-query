<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\{HQ, HtmlQuery};

class SelectorTest extends TestCase
{
    public function testQuery()
    {
        $html = '
            <div>
            <p id="title" class="title"><em>New</em>Html Query</p>
            <p id="title2" class="title"><em>New</em>Html Query2</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertTrue($hq->query('<p>PHP</p>')[0] instanceof DOMNode);
        $this->assertEquals(
            '<p>PHP</p>',
            $hq->query('<p>PHP</p>')->outerHtml()
        );

        $this->assertEquals(
            '<p id="title" class="title"><em>New</em>Html Query</p>',
            $hq->query('#title')->outerHtml()
        );

        $this->assertEquals(
            '<p id="title2" class="title"><em>New</em>Html Query2</p>',
            $hq->query('.title')->eq(1)->outerHtml()
        );

        $p = $hq->find('p');
        $this->assertEquals(
            '<p id="title2" class="title"><em>New</em>Html Query2</p>',
            ($hq('div')($p))->eq(1)->outerHtml()
        );

        $this->assertEquals(
            '<p id="title2" class="title"><em>New</em>Html Query2</p>',
            ($hq('div')($p->eq(1)->toArray()))->outerHtml()
        );

        $this->assertEquals(
            '<p id="title" class="title"><em>New</em>Html Query</p>',
            ($hq('div')($p[0]))->outerHtml()
        );
    }

    public function testId()
    {
        $html = '
            <div>
            <p id="title" class="title"><em>New</em>Html Query</p>
            <p id="title" class="title"><em>New</em>Html Query2</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            1,
            $hq('#title')->count()
        );

        $this->assertEquals(
            '<p id="title" class="title"><em>New</em>Html Query</p>',
            $hq('#title')->outerHtml()
        );

        $this->assertEquals(
            0,
            $hq('#title2')->count()
        );
    }

    public function testFind()
    {
        $html = '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar" onclick="zoom()">
                    <img src="3.png" class="bar" onclick="zoom()">
                </div>
                <p class="foo">Have fun</p>
                <p id="ts">Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            3,
            $hq->find('img')->count()
        );

        $this->assertEquals(
            2,
            $hq->find('img.bar')->count()
        );

        $this->assertEquals(
            '<p id="ts">Thanks</p>',
            $hq->find('.content')->find('#ts')->outerHtml()
        );

        $html = '
            <form method="post" action="/">
                <input name="title" type="text" value="I like it">
                <select class="foo" name="type">
                    <option value="1">PHP</option>
                    <option value="2" selected>Dom</option>
                    <option value="3">jQuery</option>
                </select>
                <textarea name="content">It\'s good.</textarea>
                <input type="submit" value="Submit">
            </form>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            3,
            $hq->find('option')->count()
        );
        $this->assertEquals(
            1,
            $hq->find("option:contains('PHP')")->count()
        );

        $html = '
            <div>
            <p id="title" class="title"><em>New</em>Html Query</p>
            <p id="title" class="title"><em>New</em>Html Query2</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            1,
            $hq->find('#title')->count()
        );

        $this->assertEquals(
            '<p id="title" class="title"><em>New</em>Html Query</p>',
            $hq->find('#title')->outerHtml()
        );

        $this->assertEquals(
            0,
            $hq->find('#title2')->count()
        );


        $html = '
            <body>
                <div class="content">
                    <img src="1.png" alt="image1">
                    <div class="image">
                        <img src="2.png" class="bar foo">
                        <img src="3.png" class="bar">
                    </div>
                    <p class="foo">Have fun</p>
                    <p>Thanks</p>
                </div>
                <img src="4.png" class="bar">
            </body>
        ';
        $hq = HQ::html($html);
        $hq->find('.content')->find($hq('.bar'))->attr('alt', 'bar');
        $this->assertHtmlEquals(
            '<body>
                <div class="content">
                    <img src="1.png" alt="image1">
                    <div class="image">
                        <img src="2.png" class="bar foo" alt="bar">
                        <img src="3.png" class="bar" alt="bar">
                    </div>
                    <p class="foo">Have fun</p>
                    <p>Thanks</p>
                </div>
                <img src="4.png" class="bar">
            </body>',
            $hq->outerHtml()
        );
    }

    public function testFilter()
    {
        $html = '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" alt="image1" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar foo" onclick="zoom()">
                    <img src="3.png" class="bar" onclick="zoom()">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            3,
            $hq->find('img')->count()
        );
        $this->assertEquals(
            2,
            $hq->find('img')->filter('.bar')->count()
        );
        $this->assertEquals(
            '<img src="1.png" alt="image1" onclick="zoom()">',
            $hq->find('img[alt="image1"]')->outerHtml()
        );
        $this->assertEquals(
            1,
            $hq->find('img')->filter('.bar.foo')->count()
        );
        $this->assertEquals(
            1,
            $hq->find('img')->filter('.bar')->filter('.foo')->count()
        );

        $this->assertEquals(
            '<img src="1.png" alt="image1" onclick="zoom()">',
            $hq->find('img')->filter(function ($index, DOMElement $node) {
                return $node->hasAttribute('alt');
            })->outerHtml()
        );

        $this->assertEquals(
            '<img src="2.png" class="bar foo" onclick="zoom()">',
            $hq->find('img')->filter(function ($index, HtmlQuery $node) {
                return $node->hasClass('foo');
            })->outerHtml()
        );

        $this->assertEquals(
            '<img src="2.png" class="bar foo" onclick="zoom()">',
            $hq->find('img')->filter($hq('img.foo'))->outerHtml()
        );

        $this->assertEquals(
            '<img src="2.png" class="bar foo" onclick="zoom()">',
            $hq->find('img')->filter($hq('img.foo')->toArray())->outerHtml()
        );
    }

    public function testParent()
    {
        $html = '
            <body>
                <div class="content">
                    <img src="1.png">
                    <img src="2.png">
                    <img src="3.png">
                    <p class="p-0">test</p>
                    <p>test2</p>
                </div>
                
                <p class="p-0">another p tag</p>
            </body>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            'div',
            $hq->find('p.p-0')->eq(0)->parent()[0]->nodeName
        );

        $this->assertEquals(
            'div',
            $hq->find('p.p-0')->eq(0)->parent('.content')[0]->nodeName
        );

        $this->assertEquals(
            0,
            $hq->find('p.p-0')->eq(0)->parent('.content1')->count()
        );

        $this->assertEquals(
            1,
            $hq->find('p.p-0')->parent('.content')->count()
        );

        $this->assertEquals(
            2,
            $hq->find('p.p-0')->parent()->count()
        );

        $hq->find('p.p-0')->parent()->each(function ($node, $index) {
            if ($index == 0) {
                $this->assertEquals('div', $node->tagName);
            }

            if ($index == 1) {
                $this->assertEquals('body', $node->tagName);
            }
        });
    }

    public function testParents()
    {
        $html = '
            <body>
                <div class="content">
                    <img src="1.png">
                    <img src="2.png">
                    <img src="3.png">
                    <p class="p-0">test</p>
                    <p>test2</p>
                </div>
                
                <p class="p-0">another p tag</p>
            </body>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            2,
            $hq->find('.content p.p-0')->parents()->count()
        );

        $this->assertEquals(
            1,
            $hq->find('p.p-0')->parents('.content')->count()
        );
    }

    public function testParentsUntil()
    {
        $html = '
            <div class="container">
                <div class="content">
                    <form target="/" method="post">
                        <select class="foo" name="type">
                            <option value="1">PHP</option>
                            <option value="2" selected>Dom</option>
                            <option value="3">jQuery</option>
                        </select>
                    </form>
                </div>
            </div>
        ';

        $hq = HQ::html($html);
        $this->assertEquals(
            'type',
            $hq->find('option:selected')->parentsUntil('form')->attr('name')
        );
    }

    public function testChildren()
    {
        $html = '
            <div>
                <p class="foo foo-bar">
                    Author: <b class="author">Thomas Su</b>
                    Date: <b class="date">2019-05-27</b>
                </p>
                <select class="foo" name="type">
                    <option value="1">PHP</option>
                    <option value="2" selected>Dom</option>
                    <option value="3">jQuery</option>
                </select>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            5,
            $hq->find('.foo')->children()->count()
        );

        $this->assertEquals(
            2,
            $hq->find('.foo')->eq(0)->children()->count()
        );

        $this->assertEquals(
            3,
            $hq->find('select.foo')->children()->count()
        );
    }

    public function testSibling()
    {
        $html = '
            <div>
                <div class="content">
                    Html Query is a jQuery like html processor written by PHP.
                    <img src="1.png" onclick="zoom()">
                    <div class="image">
                        <img src="2.png" class="bar" onclick="zoom()">
                        <img src="3.png" class="bar" onclick="zoom()">
                    </div>
                    <p class="foo">Have fun</p>
                    <p>Thanks</p>
                </div>
                <form method="post" action="/">
                    <input name="title" type="text" value="I like it">
                    <select class="foo" name="type">
                        <option value="1">PHP</option>
                        <option value="2" selected>Dom</option>
                        <option value="3">jQuery</option>
                    </select>
                    <textarea name="content">It\'s good.</textarea>
                    <input type="submit" value="Submit">
                </form>
            </div>
        ';
        $hq = HQ::html($html);

        $currentNode = $hq->find("option:contains('PHP')")->first();
        $this->assertEquals(
            2,
            $currentNode->siblings()
                ->each(function ($node) use ($currentNode) {
                    $this->assertEquals(
                        $currentNode->parent()->toArray()[0],
                        $node->parentNode
                    );
                    $this->assertNotEquals(
                        $currentNode,
                        $node
                    );
                })
                ->count()
        );
        $this->assertEquals(
            1,
            $currentNode->siblings(':selected')->count()
        );

        $this->assertEquals(
            6,
            $hq->find("select.foo, .content .foo")->siblings()->count()
        );

        $this->assertEquals(
            3,
            $hq->find("select.foo")->siblings()->count()
        );

        $this->assertEquals(
            2,
            $hq->find("select.foo, .content .foo")->siblings('input')
                ->count()
        );
    }

    public function testPrev()
    {
        $html = '
            <form method="post" action="/">
                <input name="title" type="text" value="I like it">
                <select class="foo" name="type">
                    <option value="1">PHP</option>
                    <option value="2" selected>Dom</option>
                    <option value="3">jQuery</option>
                </select>
                <textarea name="content">It\'s good.</textarea>
                <input type="submit" value="Submit">
            </form>
        ';
        $hq = HQ::html($html);

        $currentNode = $hq->find("option:contains('jQuery')")->eq(0);
        $this->assertEquals(
            'Dom',
            $currentNode->prev()->text()
        );

        $this->assertEquals(
            'Dom',
            $currentNode->prev(':selected')->text()
        );

        $this->assertEquals(
            '1',
            $currentNode->prev(":contains('PHP')")->getAttr('value')
        );

        $this->assertEquals(0, $currentNode->prev(".none")->count());

        $currentNodes = $hq->find("option:contains('jQuery')");
        $this->assertEquals(
            'Dom',
            $currentNodes->prev(':selected')->text()
        );

        $this->assertEquals(
            '1',
            $currentNodes->prev(":contains('PHP')")->getAttr('value')
        );

        $this->assertEquals(
            '1',
            $currentNodes->prev(":contains('PHP')")->getAttr('value')
        );

        $html = '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar" onclick="zoom()">
                    <img src="3.png" class="foo bar" onclick="zoom()">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);
        $this->assertEquals(
            2,
            $hq->find(".foo")->prev()->count()
        );

        $this->assertEquals(
            2,
            $hq->find(".foo")->prev('img')->count()
        );
    }

    public function testPrevAll()
    {
        $html = '
            <form method="post" action="/">
                <input name="title" type="text" value="I like it">
                <select class="foo" name="type">
                    <option value="1">PHP</option>
                    <option value="2" selected>Dom</option>
                    <option value="3">jQuery</option>
                    <option value="4">js</option>
                </select>
                <textarea name="content">It\'s good.</textarea>
                <input type="submit" value="Submit">
            </form>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            3,
            $hq->find("option:contains('js')")->prevAll()->count()
        );

        $this->assertEquals(
            'PHP',
            $hq->find("option:contains('js')")->prevAll()->html()
        );

        $this->assertEquals(
            'Dom',
            $hq->find("option:contains('js')")
                ->prevAll('[value="2"]')
                ->html()
        );
    }

    public function testPrevUntil()
    {
        $html = '
            <form method="post" action="/">
                <input name="title" type="text" value="I like it">
                <select class="foo" name="type">
                    <option value="1">PHP</option>
                    <option value="2" selected>Dom</option>
                    <option value="3">jQuery</option>
                    <option value="4">js</option>
                </select>
                <textarea name="content">It\'s good.</textarea>
                <input type="submit" value="Submit">
            </form>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            2,
            $hq->find("option:contains('js')")
                ->prevUntil("option:contains('PHP')")
                ->count()
        );
        $this->assertEquals(
            '<option value="2" selected>Dom</option>',
            $hq->find("option:contains('js')")
                ->prevUntil("option:contains('PHP')")
                ->eq(1)
                ->outerHtml()
        );

        $this->assertEquals(
            1,
            $hq->find("option:contains('js')")
                ->prevUntil("option:contains('Dom')")
                ->count()
        );

        $this->assertEquals(
            3,
            $hq->find("option:contains('js')")
                ->prevUntil("option:contains('NONE')")
                ->count()
        );
    }

    public function testNext()
    {
        $html = '
            <form method="post" action="/">
                <input name="title" type="text" value="I like it">
                <select class="foo" name="type">
                    <option value="1">PHP</option>
                    <option value="2" selected>Dom</option>
                    <option value="3">jQuery</option>
                    <option value="4">js</option>
                </select>
                <textarea name="content">It\'s good.</textarea>
                <input type="submit" value="Submit">
            </form>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            'jQuery',
            $hq->find("option:contains('Dom')")->next()->html()
        );

        $this->assertEquals(
            'jQuery',
            $hq->find("option:contains('Dom')")->next('option')->html()
        );

        $this->assertEquals(
            0,
            $hq->find("option:contains('Dom')")->next('.option')->count()
        );

        $this->assertEquals(
            0,
            $hq->find("option:contains('js')")->next()->count()
        );
    }

    public function testNextAll()
    {
        $html = '
            <form method="post" action="/">
                <input name="title" type="text" value="I like it">
                <select class="foo" name="type">
                    <option value="1">PHP</option>
                    <option value="2" selected>Dom</option>
                    <option value="3">jQuery</option>
                    <option value="4">js</option>
                </select>
                <textarea name="content">It\'s good.</textarea>
                <input type="submit" value="Submit">
            </form>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            3,
            $hq->find("option:contains('PHP')")->nextAll()->count()
        );
        $this->assertEquals(
            'jQuery',
            $hq->find("option:contains('PHP')")->nextAll()->eq(1)->html()
        );

        $this->assertEquals(
            'Dom',
            $hq->find("option:contains('PHP')")->nextAll(':selected')->html()
        );
    }

    public function testNextUntil()
    {
        $html = '
            <form method="post" action="/">
                <input name="title" type="text" value="I like it">
                <select class="foo" name="type">
                    <option value="1">PHP</option>
                    <option value="2" selected>Dom</option>
                    <option value="3">jQuery</option>
                    <option value="4">js</option>
                </select>
                <textarea name="content">It\'s good.</textarea>
                <input type="submit" value="Submit">
            </form>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            3,
            $hq->find("option:contains('PHP')")->nextUntil('.NONE')->count()
        );
        $this->assertEquals(
            2,
            $hq->find("option:contains('PHP')")
                ->nextUntil("option:contains('js')")
                ->count()
        );

        $this->assertEquals(
            0,
            $hq->find("option:contains('PHP')")->nextUntil(':selected')->count()
        );
    }

    public function testAdd()
    {
        $html = '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" class="foo" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar" onclick="zoom()">
                    <img src="3.png" class="bar" onclick="zoom()">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.image img')->add('.foo')->removeAttr('onclick');
        $this->assertHtmlEquals(
            '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" class="foo">
                <div class="image">
                    <img src="2.png" class="bar">
                    <img src="3.png" class="bar">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>',
            $hq->outerHtml()
        );
    }

    public function testIntersect()
    {
        $html = '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" class="foo" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar foo" onclick="zoom()">
                    <img src="3.png" class="bar" onclick="zoom()">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);

        $intersect = $hq->find('.image img')->intersect('.foo');
        $this->assertEquals(
            1,
            $intersect->count()
        );
        $this->assertEquals(
            '2.png',
            $intersect->attr('src')
        );

        $intersect = $hq->find('.image img')->intersect($hq('.foo'));
        $this->assertEquals(
            1,
            $intersect->count()
        );
        $this->assertEquals(
            '2.png',
            $intersect->attr('src')
        );

        $intersect = $hq->find('.image img')->intersect($hq('.foo')->toArray());
        $this->assertEquals(
            1,
            $intersect->count()
        );
        $this->assertEquals(
            '2.png',
            $intersect->attr('src')
        );

        $intersect = $hq->find('.image img')->intersect($hq('.foo')[1]);
        $this->assertEquals(
            1,
            $intersect->count()
        );
        $this->assertEquals(
            '2.png',
            $intersect->attr('src')
        );
    }

    public function testNot()
    {
        $html = '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" class="foo" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar" onclick="zoom()">
                    <img src="3.png" class="bar" onclick="zoom()">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('img')->not('.foo')->removeAttr('onclick');
        $this->assertHtmlEquals(
            '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" class="foo" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar">
                    <img src="3.png" class="bar">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>',
            $hq->outerHtml()
        );

        $this->assertEquals(
            1,
            $hq->find('img')->not('.bar')->count()
        );

        $this->assertEquals(
            '1.png',
            $hq->find('img')->not($hq('.bar'))->attr('src')
        );

        $this->assertEquals(
            '1.png',
            $hq->find('img')->not($hq('.bar')->toArray())->attr('src')
        );
    }

    public function testIs()
    {
        $html = '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" class="foo" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar" onclick="zoom()">
                    <img src="3.png" class="bar" onclick="zoom()">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertTrue($hq->find('img')->is('.foo'));
        $this->assertTrue($hq->find('img')->is($hq('.foo')));
        $this->assertTrue($hq->find('img')->is($hq('.foo')[0]));
        $this->assertTrue($hq->find('img')->is($hq('.foo')->slice(0, 2)));

        $this->assertFalse($hq->find('img')->eq(1)->is('.foo'));
        $this->assertFalse($hq->find('img')->slice(1)->is('.foo'));
        $this->assertFalse($hq->find('.null')->slice(1)->is('.foo'));
    }

    public function testInvoke()
    {
        $html = '
            <div class="content">
                Html Query is a jQuery like html processor written by PHP.
                <img src="1.png" class="foo" onclick="zoom()">
                <div class="image">
                    <img src="2.png" class="bar" onclick="zoom()">
                    <img src="3.png" class="bar" onclick="zoom()">
                </div>
                <p class="foo">Have fun</p>
                <p>Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);
        $this->assertTrue(
            $hq('.bar')->toArray() === $hq->find('.bar')->toArray()
        );

        $this->assertEquals(
            $hq->query('<p>test</p>')->outerHtml(),
            ($hq('.content')('<p>test</p>'))->outerHtml()
        );

        $this->assertEquals(
            2,
            ($hq('.content')($hq->find('.bar')->toArray()))->count()
        );

        $exception = null;
        try {
            $hq('.content')($this);
        } catch (Exception $exception) {
        }
        $this->assertInstanceOf(Exception::class, $exception);
    }
}
