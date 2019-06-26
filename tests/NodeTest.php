<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\HQ;

class NodeTest extends TestCase
{
    public function testWrap()
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
                <p>Thanks</p>
            </div>
        ';
        $hq = HQ::html($html);

        $hq->find('.image img')->wrap(
            '<div><p class="big"></p></div>'
        );
        $this->assertHtmlEquals(
            '
            <div><p class="big"><img src="2.png" class="bar" onclick="zoom()"></p></div>
            <div><p class="big"><img src="3.png" class="bar" onclick="zoom()"></p></div>',
            $hq->find('.image')->html()
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

        $this->assertNotEquals(
            'label',
            $hq->find("input[name='title']")->parent()->toArray()[0]->tagName
        );
        $hq->find('input')->wrap('<label></label>');
        $this->assertEquals(
            'label',
            $hq->find("input[name='title']")->parent()->toArray()[0]->tagName
        );
        $this->assertEquals(
            'label',
            $hq->find("input[type='submit']")->parent()->toArray()[0]->tagName
        );

        $html = '
            <div class="content">
                <img src="1.png" onclick="zoom()">
                <div class="image">
                    <div class="big-image">
                        <p></p>
                    </div>
                </div>
            </div>
        ';
        $hq = HQ::html($html);

        $hq->find('img')->wrap($hq('.none'));
        $this->assertHtmlEquals($html, $hq->outerHtml());

        $hq->find('img')->wrap($hq('.image'));
        $this->assertHtmlEquals(
            '
            <div class="content">
                <div class="image">
                    <div class="big-image">
                        <p>
                            <img src="1.png" onclick="zoom()">
                        </p>
                    </div>
                </div>
            </div>',
            $hq->outerHtml()
        );
    }

    public function testWrapInner()
    {
        $html = '
            <div class="image">
                <img src="2.png" class="bar" onclick="zoom()">
                <img src="3.png" class="bar" onclick="zoom()">
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertHtmlEquals(
            '<div><p class="big">
                <img src="2.png" class="bar" onclick="zoom()">
                <img src="3.png" class="bar" onclick="zoom()">
            </p></div>',
            $hq->find('div.image')
                ->wrapInner('<div><p class="big"></p></div>')
                ->html()
        );

        $html = '
            <p class="foo foo-bar">
                Author: <b class="author">Thomas Su</b>
                Date: <b class="date">2019-05-27</b>
            </p>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            '<em>Thomas Su</em>',
            $hq->find('.author')
                ->wrapInner('<em></em>')
                ->html()
        );

        $html = '
            <div class="content">
                <div class="img-container">
                    <img src="1.png" onclick="zoom()">
                </div>
                <div class="image">
                    <div class="big-image">
                        <p></p>
                    </div>
                </div>
            </div>
        ';
        $hq = HQ::html($html);

        $hq->find('.img-container')->wrapInner($hq('.none'));
        $this->assertHtmlEquals($html, $hq->outerHtml());

        $hq->find('.img-container')->wrapInner($hq('.image'));
        $this->assertHtmlEquals(
            '
            <div class="content">
                <div class="img-container">
                    <div class="image">
                        <div class="big-image">
                            <p>
                                <img src="1.png" onclick="zoom()">
                            </p>
                        </div>
                    </div>
                </div>
            </div>',
            $hq->outerHtml()
        );
    }

    public function testWrapAll()
    {
        $html = '
            <div class="tag">
                <li><a href="/php">PHP</a></li>
                <li><a href="/dom">Dom</a></li>
                <li><a href="/jquery">jQuery</a></li>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('li')->wrapAll('<ul></ul>');
        $this->assertHtmlEquals(
            '<div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li><a href="/dom">Dom</a></li>
                    <li><a href="/jquery">jQuery</a></li>
                </ul>
            </div>',
            $hq->find('.tag')->outerHtml()
        );

        $html = '
            <div class="tag">
                <div>
                    <li><a href="/php">PHP</a></li>
                    <li><a href="/dom">Dom</a></li>
                    <li><a href="/jquery">jQuery</a></li>
                </div>
                <li>js</li>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('li')->wrapAll('<ul></ul>');
        $this->assertHtmlEquals(
            '<div class="tag">
                <div>
                    <ul>
                        <li><a href="/php">PHP</a></li>
                        <li><a href="/dom">Dom</a></li>
                        <li><a href="/jquery">jQuery</a></li>
                        <li>js</li>
                    </ul>
                </div>
            </div>',
            $hq->find('.tag')->outerHtml()
        );

        $html = '
            <div class="content">
                <div class="img-container">
                    <img src="1.png" onclick="zoom()">
                    <img src="2.png" onclick="zoom()">
                </div>
                <img src="3.png" onclick="zoom()">
                <div class="image">
                    <div class="big-image">
                        <p></p>
                    </div>
                </div>
            </div>
        ';
        $hq = HQ::html($html);

        $hq->find('.img-container')->wrapAll($hq('.none'));
        $this->assertHtmlEquals($html, $hq->outerHtml());

        $hq->find('img')->wrapAll($hq('.image'));
        $this->assertHtmlEquals(
            '
            <div class="content">
                <div class="img-container">
                    <div class="image">
                        <div class="big-image">
                            <p>
                                <img src="1.png" onclick="zoom()">
                                <img src="2.png" onclick="zoom()">
                                <img src="3.png" onclick="zoom()">
                            </p>
                        </div>
                    </div>
                </div>
            </div>',
            $hq->outerHtml()
        );
    }

    public function testUnwrap()
    {
        $html = '
            <div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li><a href="/dom">Dom</a></li>
                    <li><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);

        $hq->find('.tag li')->unwrap();
        $this->assertEquals(
            '
                
                    <li><a href="/php">PHP</a></li>
                    <li><a href="/dom">Dom</a></li>
                    <li><a href="/jquery">jQuery</a></li>
                
            ',
            $hq->find('.tag')->html()
        );
    }

    public function testUnwrapSelf()
    {
        $html = '
            <div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li><a href="/dom">Dom</a></li>
                    <li><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);

        $hq->find('.tag ul li a')->unwrapSelf();
        $this->assertEquals(
            '
                    <li>PHP</li>
                    <li>Dom</li>
                    <li>jQuery</li>
                ',
            $hq->find('.tag ul')->html()
        );

        $hq->find('div.image img')->unwrapSelf();
        $this->assertEquals(
            '',
            trim($hq->find('div.image')->html())
        );
    }

    public function testBefore()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.front')->before('<li>front end</li>');
        $this->assertHtmlEquals(
            '
            <li><a href="/php">PHP</a></li>
            <li>front end</li>
            <li class="front"><a href="/dom">Dom</a></li>
            <li>front end</li>
            <li class="front"><a href="/jquery">jQuery</a></li>',
            $hq->find('ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.tag')->before($hq->find('h2')[0]);
        $this->assertHtmlEquals(
            '<h2>Hog tags</h2><div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>',
            $hq->outerHtml()
        );

        $html = '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <div class="inner">Hello</div>
                    <h2>Greetings 2</h2>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <h2>Greetings 3</h2>
                    <p>Test</p>
                </div>
                <div class="container">test</div>
            </body>
        ';
        $hq = HQ::html($html);
        $hq('.container')->before($hq('h2'));
        $this->assertHtmlEquals(
            '
            <body>
                <h2>Greetings</h2>
                <h2>Greetings 2</h2>
                <h2>Greetings 3</h2>
                <div class="container">
                    <div class="inner">Hello</div>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <p>Test</p>
                </div>
                <h2>Greetings</h2>
                <h2>Greetings 2</h2>
                <h2>Greetings 3</h2>
                <div class="container">test</div>
            </body>',
            $hq->outerHtml()
        );
    }

    public function testInsertBefore()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('h2')->insertBefore('.tag');
        $this->assertHtmlEquals(
            '<h2>Hog tags</h2><div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>',
            $hq->outerHtml()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
                <h2>Hog tags2</h2>
            </div>
            <div class="tag"></div>
        ';
        $hq = HQ::html($html);
        $hq->find('h2')->insertBefore('.tag');
        $this->assertHtmlEquals(
            '
            <h2>Hog tags</h2>
            <h2>Hog tags2</h2>
            <div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
                <h2>Hog tags</h2>
                <h2>Hog tags2</h2>
                <div class="tag"></div>
            </div>',
            $hq->outerHtml()
        );

        $html = '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <div class="inner">Hello</div>
                    <h2>Greetings 2</h2>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <h2>Greetings 3</h2>
                    <p>Test</p>
                </div>
                <div class="container">test</div>
            </body>
        ';
        $hq = HQ::html($html);
        $hq('h2')->insertBefore('.container');
        $this->assertHtmlEquals(
            '
            <body>
                <h2>Greetings</h2>
                <h2>Greetings 2</h2>
                <h2>Greetings 3</h2>
                <div class="container">
                    <div class="inner">Hello</div>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <p>Test</p>
                </div>
                <h2>Greetings</h2>
                <h2>Greetings 2</h2>
                <h2>Greetings 3</h2>
                <div class="container">test</div>
            </body>',
            $hq->outerHtml()
        );
    }

    public function testAfter()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.front')->after('<li>front end</li>');
        $this->assertHtmlEquals(
            '
            <li><a href="/php">PHP</a></li>
            <li class="front"><a href="/dom">Dom</a></li>
            <li>front end</li>
            <li class="front"><a href="/jquery">jQuery</a></li>
            <li>front end</li>',
            $hq->find('ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('ul')->after($hq->find('h2')[0]);
        $this->assertHtmlEquals(
            '<div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
                <h2>Hog tags</h2>
            </div>',
            $hq->outerHtml()
        );

        $html = '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <div class="inner">Hello</div>
                    <h2>Greetings 2</h2>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <h2>Greetings 3</h2>
                    <p>Test</p>
                </div>
                <div class="container">test</div>
            </body>
        ';
        $hq = HQ::html($html);
        $hq('.container')->after($hq('h2'));
        $this->assertHtmlEquals(
            '
            <body>
                <div class="container">
                    <div class="inner">Hello</div>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <p>Test</p>
                </div>
                <h2>Greetings</h2>
                <h2>Greetings 2</h2>
                <h2>Greetings 3</h2>
                <div class="container">test</div>
                <h2>Greetings</h2>
                <h2>Greetings 2</h2>
                <h2>Greetings 3</h2>
            </body>',
            $hq->outerHtml()
        );
    }

    public function testInsertAfter()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('h2')->insertAfter('ul');
        $this->assertHtmlEquals(
            '<div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
                <h2>Hog tags</h2>
            </div>',
            $hq->outerHtml()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <h2>Hog tags2</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                </ul>
                <ul>
                    <li class="front"><a href="/dom">Dom</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('h2')->insertAfter('ul');
        $this->assertHtmlEquals(
            '<div class="tag">
                <ul>
                    <li><a href="/php">PHP</a></li>
                </ul>
                <h2>Hog tags</h2>
                <h2>Hog tags2</h2>
                <ul>
                    <li class="front"><a href="/dom">Dom</a></li>
                </ul>
                <h2>Hog tags</h2>
                <h2>Hog tags2</h2>
            </div>',
            $hq->outerHtml()
        );

        $html = '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <div class="inner">Hello</div>
                    <h2>Greetings 2</h2>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <h2>Greetings 3</h2>
                    <p>Test</p>
                </div>
                <div class="container">test</div>
            </body>
        ';
        $hq = HQ::html($html);
        $hq('h2')->insertAfter('.container');
        $this->assertHtmlEquals(
            '
            <body>
                <div class="container">
                    <div class="inner">Hello</div>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <p>Test</p>
                </div>
                <h2>Greetings</h2>
                <h2>Greetings 2</h2>
                <h2>Greetings 3</h2>
                <div class="container">test</div>
                <h2>Greetings</h2>
                <h2>Greetings 2</h2>
                <h2>Greetings 3</h2>
            </body>',
            $hq->outerHtml()
        );
    }

    public function testAppend()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('ul')->append('<li>js</li>');
        $this->assertHtmlEquals(
            '
            <li><a href="/php">PHP</a></li>
            <li class="front"><a href="/dom">Dom</a></li>
            <li class="front"><a href="/jquery">jQuery</a></li>
            <li>js</li>',
            $hq->find('ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('li')->append('<em>new</em>');
        $this->assertHtmlEquals(
            '
            <li><a href="/php">PHP</a><em>new</em></li>
            <li class="front"><a href="/dom">Dom</a><em>new</em></li>
            <li class="front"><a href="/jquery">jQuery</a><em>new</em></li>',
            $hq->find('ul')->html()
        );

        $html = '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <div class="inner">Hello</div>
                    <h2>Greetings 2</h2>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <h2>Greetings 3</h2>
                    <p>Test</p>
                </div>
                <div class="container">
                    <p>test</p>
                </div>
            </body>
        ';
        $hq = HQ::html($html);
        $hq('.container')->append($hq('h2'));
        $this->assertHtmlEquals(
            '
            <body>
                <div class="container">
                    <div class="inner">Hello</div>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <p>Test</p>
                    <h2>Greetings</h2>
                    <h2>Greetings 2</h2>
                    <h2>Greetings 3</h2>
                </div>
                <div class="container">
                    <p>test</p>
                    <h2>Greetings</h2>
                    <h2>Greetings 2</h2>
                    <h2>Greetings 3</h2>
                </div>
            </body>',
            $hq->outerHtml()
        );
    }

    public function testAppendTo()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->query('<li>js</li>')->appendTo('ul');
        $this->assertHtmlEquals(
            '
            <li><a href="/php">PHP</a></li>
            <li class="front"><a href="/dom">Dom</a></li>
            <li class="front"><a href="/jquery">jQuery</a></li>
            <li>js</li>',
            $hq->find('ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->query('<em>new</em>')->appendTo('li');
        $this->assertHtmlEquals(
            '
            <li><a href="/php">PHP</a><em>new</em></li>
            <li class="front"><a href="/dom">Dom</a><em>new</em></li>
            <li class="front"><a href="/jquery">jQuery</a><em>new</em></li>',
            $hq->find('ul')->html()
        );

        $html = '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <div class="inner">Hello</div>
                    <h2>Greetings 2</h2>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <h2>Greetings 3</h2>
                    <p>Test</p>
                </div>
                <div class="container">
                    <p>test</p>
                </div>
            </body>
        ';
        $hq = HQ::html($html);
        $hq('h2')->appendTo('.container');
        $this->assertHtmlEquals(
            '
            <body>
                <div class="container">
                    <div class="inner">Hello</div>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <p>Test</p>
                    <h2>Greetings</h2>
                    <h2>Greetings 2</h2>
                    <h2>Greetings 3</h2>
                </div>
                <div class="container">
                    <p>test</p>
                    <h2>Greetings</h2>
                    <h2>Greetings 2</h2>
                    <h2>Greetings 3</h2>
                </div>
            </body>',
            $hq->outerHtml()
        );
    }

    public function testPrepend()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('ul')->prepend('<li>js</li>');
        $this->assertHtmlEquals(
            '
            <li>js</li>
            <li><a href="/php">PHP</a></li>
            <li class="front"><a href="/dom">Dom</a></li>
            <li class="front"><a href="/jquery">jQuery</a></li>',
            $hq->find('ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('li')->prepend('<em>new</em>');
        $this->assertHtmlEquals(
            '
            <li><em>new</em><a href="/php">PHP</a></li>
            <li class="front"><em>new</em><a href="/dom">Dom</a></li>
            <li class="front"><em>new</em><a href="/jquery">jQuery</a></li>',
            $hq->find('ul')->html()
        );

        $html = '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <div class="inner">Hello</div>
                    <h2>Greetings 2</h2>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <h2>Greetings 3</h2>
                    <p>Test</p>
                </div>
                <div class="container">
                    <p>test</p>
                </div>
            </body>
        ';
        $hq = HQ::html($html);
        $hq('.container')->prepend($hq('h2'));
        $this->assertHtmlEquals(
            '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <h2>Greetings 2</h2>
                    <h2>Greetings 3</h2>
                    <div class="inner">Hello</div>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <p>Test</p>
                </div>
                <div class="container">
                    <h2>Greetings</h2>
                    <h2>Greetings 2</h2>
                    <h2>Greetings 3</h2>
                    <p>test</p>
                </div>
            </body>',
            $hq->outerHtml()
        );
    }

    public function testPrependTo()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->query('<li>js</li>')->prependTo('ul');
        $this->assertHtmlEquals(
            '
            <li>js</li>
            <li><a href="/php">PHP</a></li>
            <li class="front"><a href="/dom">Dom</a></li>
            <li class="front"><a href="/jquery">jQuery</a></li>',
            $hq->find('ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->query('<em>new</em>')->prependTo('li');
        $this->assertHtmlEquals(
            '
            <li><em>new</em><a href="/php">PHP</a></li>
            <li class="front"><em>new</em><a href="/dom">Dom</a></li>
            <li class="front"><em>new</em><a href="/jquery">jQuery</a></li>',
            $hq->find('ul')->html()
        );

        $html = '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <div class="inner">Hello</div>
                    <h2>Greetings 2</h2>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <h2>Greetings 3</h2>
                    <p>Test</p>
                </div>
                <div class="container">
                    <p>test</p>
                </div>
            </body>
        ';
        $hq = HQ::html($html);
        $hq('h2')->prependTo('.container');
        $this->assertHtmlEquals(
            '
            <body>
                <div class="container">
                    <h2>Greetings</h2>
                    <h2>Greetings 2</h2>
                    <h2>Greetings 3</h2>
                    <div class="inner">Hello</div>
                    <p>Test</p>
                    <div class="inner">Goodbye</div>
                    <p>Test</p>
                </div>
                <div class="container">
                    <h2>Greetings</h2>
                    <h2>Greetings 2</h2>
                    <h2>Greetings 3</h2>
                    <p>test</p>
                </div>
            </body>',
            $hq->outerHtml()
        );
    }

    public function testReplaceWith()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul class="ul">
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.ul li')->replaceWith('<li>js</li>');
        $this->assertHtmlEquals(
            '
            <li>js</li>
            <li>js</li>
            <li>js</li>',
            $hq->find('.ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul class="ul">
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
                <ul class="ul2">
                    <li>css</li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.ul li')->replaceWith($hq->find('.ul2 li'));
        $this->assertHtmlEquals(
            '
            <li>css</li>
            <li>css</li>
            <li>css</li>',
            $hq->find('.ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul class="ul">
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
                <ul class="ul2">
                    <li>css</li>
                    <li>html</li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.ul li')->replaceWith($hq->find('.ul2 li'));
        $this->assertHtmlEquals(
            '
            <li>css</li>
            <li>html</li>
            <li>css</li>
            <li>html</li>
            <li>css</li>
            <li>html</li>',
            $hq->find('.ul')->html()
        );
    }

    public function testReplaceAll()
    {
        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul>
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->query('<li>js</li>')->replaceAll('li');
        $this->assertHtmlEquals(
            '
            <li>js</li>
            <li>js</li>
            <li>js</li>',
            $hq->find('ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul class="ul">
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
                <ul class="ul2">
                    <li>css</li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.ul2 li')->replaceAll($hq->find('.ul li'));
        $this->assertHtmlEquals(
            '
            <li>css</li>
            <li>css</li>
            <li>css</li>',
            $hq->find('.ul')->html()
        );

        $html = '
            <div class="tag">
                <h2>Hog tags</h2>
                <ul class="ul">
                    <li><a href="/php">PHP</a></li>
                    <li class="front"><a href="/dom">Dom</a></li>
                    <li class="front"><a href="/jquery">jQuery</a></li>
                </ul>
                <ul class="ul2">
                    <li>css</li>
                    <li>html</li>
                </ul>
            </div>
        ';
        $hq = HQ::html($html);
        $hq->find('.ul2 li')->replaceAll($hq->find('.ul li'));
        $this->assertHtmlEquals(
            '
            <li>css</li>
            <li>html</li>
            <li>css</li>
            <li>html</li>
            <li>css</li>
            <li>html</li>',
            $hq->find('.ul')->html()
        );
    }
}
