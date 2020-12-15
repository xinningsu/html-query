<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\{HQ, HtmlQuery};

class ExampleTest extends TestCase
{
    public function testGetContents()
    {
        $html = '
            <html>
            <head>
                <title>Html Query</title>
            </head>
            <body>
                <h1 class="title">this is title</h1>
                <div class="content">this is <b>content</b>...</div>
            </body>
            </html>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            'this is title',
            $hq('.title')->html()
        );
        $this->assertEquals(
            'this is <b>content</b>...',
            $hq('.content')->html()
        );
        $this->assertEquals(
            '<div class="content">this is <b>content</b>...</div>',
            $hq('.content')->outerHtml()
        );
        $this->assertEquals(
            'this is content...',
            $hq('.content')->text()
        );
    }

    public function testSetContents()
    {
        $html = '
            <html>
            <head>
                <title>Html Query</title>
            </head>
            <body>
                <h1 class="title">this is title</h1>
                <div class="content">this is <b>content</b>...</div>
            </body>
            </html>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            'this is new title',
            $hq('.title')->html('this is new title')->html()
        );
        $this->assertEquals(
            'this is <b>new content</b>...',
            $hq('.content')->html('this is <b>new content</b>...')->html()
        );

        $this->assertEquals(
            'this is new content...',
            $hq('.content')->text('this is new content...')->html()
        );
    }

    public function testGetAttributes()
    {
        $html = '
            <div class="container">
                <img src="1.png">
                <img src="2.png">
                <div class="img">
                    <img src="3.png">
                </div>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            ['1.png', '2.png', '3.png'],
            $hq('.container img')->map(function (DOMElement $node) {
                return $node->getAttribute('src');
            })
        );

        $this->assertEquals(
            ['1.png', '2.png', '3.png'],
            $hq('.container img')->map(function (HtmlQuery $node) {
                return $node->attr('src');
            })
        );

        $this->assertEquals(
            '2.png',
            $hq('.container img')->eq(1)->attr('src')
        );
        $this->assertEquals(
            '2.png',
            $hq('.container img')[1]->getAttribute('src')
        );
    }

    public function testChangeAttributes()
    {
        $html = '
            <div class="container">
                <img src="1.png" width="100" onclick="zoom()">
                <img src="2.png" width="100" onclick="zoom()">
                <div class="img">
                    <img src="3.png" width="200" data-src="3" onclick="zoom()">
                </div>
            </div>
        ';
        $hq = HQ::html($html);
        $images = $hq('.container img');

        $images->removeAttr('onclick');
        $this->assertHtmlEquals(
            '
            <div class="container">
                <img src="1.png" width="100">
                <img src="2.png" width="100">
                <div class="img">
                    <img src="3.png" width="200" data-src="3">
                </div>
            </div>',
            $hq('.container')->outerHtml()
        );

        $images->removeAllAttrs(['src']);
        $this->assertHtmlEquals(
            '
            <div class="container">
                <img src="1.png">
                <img src="2.png">
                <div class="img">
                    <img src="3.png">
                </div>
            </div>',
            $hq('.container')->outerHtml()
        );

        $images->each(function (DOMElement $node) {
            $node->setAttribute('title', 'html query');
        });

        $images->each(function (HtmlQuery $node, $index) {
            $node->attr(['alt' => 'image ' . ($index + 1)]);
        });

        $this->assertHtmlEquals(
            '
            <div class="container">
                <img src="1.png" title="html query" alt="image 1">
                <img src="2.png" title="html query" alt="image 2">
                <div class="img">
                    <img src="3.png" title="html query" alt="image 3">
                </div>
            </div>',
            $hq('.container')->outerHtml()
        );
    }

    public function testChangeStructure()
    {
        $html = '
            <div class="container">
                <img src="1.png">
                <img src="2.png">
                <div class="img">
                    <img src="3.png">
                </div>
            </div>
        ';
        $hq = HQ::html($html);

        $hq('.container img')->not('.img img')->wrap('<div class="img"></div>');
        $this->assertHtmlEquals(
            '
            <div class="container">
                <div class="img">
                    <img src="1.png">
                </div>
                <div class="img">
                    <img src="2.png">
                </div>
                <div class="img">
                    <img src="3.png">
                </div>
            </div>',
            $hq('.container')->outerHtml()
        );

        $hq('.container img')->unwrap();
        $this->assertHtmlEquals(
            '
            <div class="container">
                <img src="1.png">
                <img src="2.png">
                <img src="3.png">
            </div>',
            $hq('.container')->outerHtml()
        );

        $hq('<img src="0.png"/>')->prependTo('.container');
        $hq->find('.container')->append('<img src="4.png"/>');
        $this->assertHtmlEquals(
            '
            <div class="container">
                <img src="0.png">
                <img src="1.png">
                <img src="2.png">
                <img src="3.png">
                <img src="4.png">
            </div>',
            $hq('.container')->outerHtml()
        );

        $hq("img[src='0.png']")->remove();
        $hq("img[src='2.png']")->after('<img src="2.5.png"/>');
        $hq("img[src='1.png']")->before($hq("img[src='4.png']"));
        $this->assertHtmlEquals(
            '
            <div class="container">
                <img src="4.png">
                <img src="1.png">
                <img src="2.png">
                <img src="2.5.png">
                <img src="3.png">
            </div>',
            $hq('.container')->outerHtml()
        );
    }
}
