<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\HQ;

class ContentTest extends TestCase
{
    public function testVal()
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
        $this->assertEquals(
            'I like it',
            $hq->find("input[name='title']")->val()
        );

        $this->assertEquals(
            '2',
            $hq->find("select[name='type']")->val()
        );

        $this->assertEquals(
            "It's good.",
            $hq->find("textarea[name='content']")->val()
        );

        $this->assertEquals(
            'I like it very much',
            $hq->find("input[name='title']")
                ->val('I like it very much')
                ->val()
        );

        $this->assertEquals(
            '3',
            $hq->find("select[name='type']")->val(3)->val()
        );

        $this->assertEquals(
            "It's really good.",
            $hq->find("textarea[name='content']")
                ->val("It's really good.")
                ->val()
        );
    }

    public function testHtml()
    {
        $html = '
            <div class="content">
                <img src="1.png">
                <img src="2.png">
                <img src="3.png">
                <p class="p-0">test</p>
                <p>test2</p>
            </div>
        ';
        $hq = HQ::html($html);
        $this->assertHtmlEquals(
            '
            <img src="1.png">
            <img src="2.png">
            <img src="3.png">
            <p class="p-0">test</p>
            <p>test2</p>',
            $hq->find('.content')->html()
        );

        $this->assertEquals(
            $hq->find('p')->html(),
            'test'
        );
        $this->assertEquals(
            $hq->find('p')->eq(0)->getHtml(),
            'test'
        );
        $this->assertEquals(
            $hq->find('p')->eq(1)->html(),
            'test2'
        );

        $hq->find('.content')->html(
            '<a href="test.php" target="_blank">Test</a> Test link'
        );
        $this->assertEquals(
            '<a href="test.php" target="_blank">Test</a> Test link',
            $hq->find('.content')->html()
        );

        $hq->find('.content')->setHtml('');
        $this->assertEquals(
            '',
            $hq->find('.content')->html()
        );
    }

    public function testOuterHtml()
    {
        $html = '
            <div class="content">
                <img src="1.png">
                <img src="2.png">
                <img src="3.png">
                <p class="p-0">test</p>
                <p>test2</p>
            </div>
        ';
        $hq = HQ::html($html);
        $this->assertHtmlEquals(
            '<div class="content">
                <img src="1.png">
                <img src="2.png">
                <img src="3.png">
                <p class="p-0">test</p>
                <p>test2</p>
            </div>',
            $hq->find('.content')->outerHtml()
        );

        $this->assertEquals(
            $hq->find('p')->outerHtml(),
            '<p class="p-0">test</p>'
        );
        $this->assertEquals(
            $hq->find('p')->eq(0)->outerHtml(),
            '<p class="p-0">test</p>'
        );
        $this->assertEquals(
            $hq->find('p')->eq(1)->outerHtml(),
            '<p>test2</p>'
        );
    }

    public function testText()
    {
        $html = '
            <div class="content">
                <img src="1.png">
                <img src="2.png">
                <img src="3.png">
                <p class="p-0">test</p>
                <p>test2</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            'test
                test2',
            trim($hq->find('.content')->text())
        );

        $this->assertEquals(
            'test',
            $hq->find('p')->getText()
        );
        $this->assertEquals(
            'test',
            $hq->find('p')->eq(0)->text()
        );
        $this->assertEquals(
            'test2',
            $hq->find('p')->eq(1)->text()
        );

        $this->assertEquals(
            'new text',
            $hq->find('p')->eq(1)->text('new text')->text()
        );

        $this->assertEquals(
            'test',
            $hq->find('p')->eq(1)->setText('test')->text()
        );
    }

    public function testEmpty()
    {
        $html = '
            <div class="content">
                <p class="p-0 p-1">test</p>
            </div>
        ';
        $hq = HQ::html($html);

        $hq->find('p')->empty();
        $this->assertEquals(
            '',
            $hq->find('p')->text()
        );
        $this->assertEquals(
            '',
            $hq->find('p')->html()
        );
        $this->assertEquals(
            '<p class="p-0 p-1"></p>',
            $hq->find('p')->outerHtml()
        );

        $hq->find('.content')->empty();
        $this->assertEquals(
            '<div class="content"></div>',
            $hq->find('.content')->outerHtml()
        );
    }
}
