<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\{HQ, HtmlQuery};

class HQTest extends TestCase
{
    public function testHtml()
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

        $this->assertInstanceOf(HtmlQuery::class, $hq);
        $this->assertCount(1, $hq);
        $this->assertHtmlEquals($html, $hq->outerHtml());
    }

    public function testHtmlFile()
    {
        $file = __DIR__ . '/test.html';
        $hq = HQ::htmlFile($file);

        $this->assertInstanceOf(HtmlQuery::class, $hq);
        $this->assertCount(1, $hq);
        $this->assertHtmlEquals(file_get_contents($file), $hq->outerHtml());
    }

    public function testHtmlInstance()
    {
        $hq = HQ::instance();
        $this->assertInstanceOf(HtmlQuery::class, $hq);
        $this->assertCount(1, $hq);
        $this->assertEmpty(trim($hq->outerHtml()));

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
        $hq = HQ::instance($html);

        $this->assertInstanceOf(HtmlQuery::class, $hq);
        $this->assertCount(1, $hq);
        $this->assertHtmlEquals($html, $hq->outerHtml());
    }
}
