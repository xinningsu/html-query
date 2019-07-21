<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\HQ;

class AttributeTest extends TestCase
{
    public function testAttr()
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
            '1.png',
            $hq->find('img')->attr('src')
        );
        $this->assertEquals(
            '1.png',
            $hq->find('img')->eq(0)->getAttr('src')
        );

        $this->assertEquals(
            '2.png',
            $hq->find('img')->eq(1)->attr('src')
        );

        $this->assertEquals(
            '3.png',
            $hq->find('img')->eq(2)->attr('src')
        );

        $this->assertEquals(
            'img 1 title',
            $hq->find('img')->eq(0)->attr('title', 'img 1 title')
                ->attr('title')
        );

        $hq->find('img')->eq(1)->attr([
            'alt' => 'image 2 alt',
            'height' => 100,
            'width' => 150,
        ]);
        $this->assertEquals(
            'image 2 alt',
            $hq->find('img')->eq(1)->attr('alt')
        );
        $this->assertEquals(
            100,
            $hq->find('img')->eq(1)->attr('height')
        );
        $this->assertEquals(
            150,
            $hq->find('img')->eq(1)->attr('width')
        );

        $this->assertEquals(
            100,
            $hq->find('img')->eq(1)->setAttr('width', 100)->attr('width')
        );

        $this->assertNull($hq->attr('none'));
    }

    public function testRemoveAttr()
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
            '<img>',
            $hq->find('img')->eq(1)->removeAttr('src')->outerHtml()
        );

        $hq->find('img')->removeAttr('src');
        $this->assertHtmlEquals(
            '<div class="content">
                <img>
                <img>
                <img>
                <p class="p-0">test</p>
                <p>test2</p>
            </div>',
            $hq->find('.content')->outerHtml()
        );
    }

    public function testRemoveAllAttrs()
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

        $hq->find('img')->eq(1)->attr([
            'src' => '2.1.png',
            'alt' => 'image 2 alt',
            'height' => 100,
            'width' => 150,
        ]);

        $this->assertEquals(
            '<img src="2.1.png" alt="image 2 alt" height="100" width="150">',
            $hq->find('img')->eq(1)->outerHtml()
        );

        $this->assertEquals(
            '<img src="2.1.png">',
            $hq->find('img')->eq(1)->removeAllAttrs(['src'])->outerHtml()
        );

        $this->assertEquals(
            '<img>',
            $hq->find('img')->eq(1)->removeAllAttrs()->outerHtml()
        );
    }

    public function testHasAttr()
    {
        $html = '
            <div class="content">
                <img src="1.png" alt="1 png">
                <img src="2.png">
                <img src="3.png">
                <p class="p-0">test</p>
                <p>test2</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertTrue($hq->find('p')->hasAttr('class'));
        $this->assertFalse($hq->find('p')->hasAttr('class1'));

        $this->assertTrue($hq->find('img')->eq(0)->hasAttr('alt'));
        $this->assertFalse($hq->find('img')->eq(1)->hasAttr('alt'));

        $this->assertFalse($hq->hasAttr('alt'));
    }

    public function testProp()
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
            '1.png',
            $hq->find('img')->prop('src')
        );

        $this->assertEquals(
            'img 1 title',
            $hq->find('img')->eq(0)->prop('title', 'img 1 title')
                ->attr('title')
        );
    }

    public function testRemoveProp()
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
            '<img>',
            $hq->find('img')->eq(1)->removeProp('src')->outerHtml()
        );

        $hq->find('img')->removeProp('src');
        $this->assertHtmlEquals(
            '<div class="content">
                <img>
                <img>
                <img>
                <p class="p-0">test</p>
                <p>test2</p>
            </div>',
            $hq->find('.content')->outerHtml()
        );
    }

    public function testData()
    {
        $html = '
            <div class="content">
                <p class="p-0" data-id="1">test</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            '1',
            $hq->find('.p-0')->data('id')
        );

        $this->assertEquals(
            '<p class="p-0" data-id="2">test</p>',
            $hq->find('.p-0')->data('id', 2)->outerHtml()
        );

        $this->assertEquals(
            '<p class="p-0" data-id="2" data-name="test">test</p>',
            $hq->find('.p-0')->data(['name' => 'test'])->outerHtml()
        );

        $hq->find('.p-0')
            ->data('content', ['id' => 1, 'tag' => 'dom'])
            ->removeData('id')
            ->removeData('name');

        $this->assertEquals(
            '<p class="p-0" data-content=\'{"id":1,"tag":"dom"}\'>test</p>',
            $hq->find('.p-0')->outerHtml()
        );

        $data = $hq->find('.p-0')->data('content');
        $this->assertEquals(1, $data->id);
        $this->assertEquals('dom', $data->tag);
    }

    public function testHasData()
    {
        $html = '
            <div class="content">
                <p class="p-0" data-id="1">test</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertTrue($hq->find('.p-0')->hasData('id'));
        $this->assertFalse($hq->find('.p-0')->hasData('id1'));
        $this->assertTrue($hq->find('.p-0')->data('id1', '2')->hasData('id1'));
    }

    public function testRemoveData()
    {
        $html = '
            <div class="content">
                <p class="p-0" data-id="1">test</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            '<p class="p-0">test</p>',
            $hq->find('.p-0')->removeData('id')->outerHtml()
        );

        $this->assertEquals(
            '<p class="p-0">test</p>',
            $hq->find('.p-0')->removeData('id2')->outerHtml()
        );
    }

    public function testAddClass()
    {
        $html = '
            <div class="content">
                <p class="p-0" data-id="1">test</p>
            </div>
        ';
        $hq = HQ::html($html);

        $this->assertEquals(
            '<p class="p-0 p-1" data-id="1">test</p>',
            $hq->find('.p-0')->addClass('p-1')->outerHtml()
        );

        $this->assertEquals(
            '<p class="p-0 p-1" data-id="1">test</p>',
            $hq->find('.p-0')->addClass('p-0')->outerHtml()
        );

        $this->assertEquals(
            '<p class="p-0 p-1 p-3" data-id="1">test</p>',
            $hq->find('.p-0')->addClass('p-3')->outerHtml()
        );
    }

    public function testHasClass()
    {
        $html = '
            <div class="content">
                <p class="p-0" data-id="1">test</p>
            </div>
        ';
        $hq = HQ::html($html);
        $this->assertTrue($hq->find('.p-0')->hasClass('p-0'));
        $this->assertFalse($hq->find('.p-0')->hasClass('p-1'));
        $hq->find('.p-0')->addClass('p-1');
        $this->assertTrue($hq->find('.p-0')->hasClass('p-1'));
    }

    public function testRemoveClass()
    {
        $html = '
            <div class="content">
                <p class="p-0 p-1">test</p>
            </div>
        ';
        $hq = HQ::html($html);
        $this->assertEquals(
            '<p class="p-0 p-1">test</p>',
            $hq->find('.p-0')->removeClass('p-3')->outerHtml()
        );

        $this->assertEquals(
            '<p class="p-0">test</p>',
            $hq->find('.p-0')->removeClass('p-1')->outerHtml()
        );

        $this->assertEquals(
            '<p>test</p>',
            $hq->find('.p-0')->removeClass('p-0')->outerHtml()
        );

        ;
        $this->assertEquals(
            '<p class="p-3 p-4">test</p>',
            $hq->find('p')->addClass('p-3 p-4')->outerHtml()
        );

        $this->assertEquals(
            '<p>test</p>',
            $hq->find('p')->removeClass()->outerHtml()
        );

        $this->assertEquals(
            '<p>test</p>',
            $hq->find('p')->removeClass('test')->outerHtml()
        );
    }

    public function testToggleClass()
    {
        $html = '
            <div class="content">
                <p class="p-0 p-1">test</p>
            </div>
        ';
        $hq = HQ::html($html);
        $this->assertEquals(
            '<p class="p-0 p-2">test</p>',
            $hq->find('p')->toggleClass('p-1 p-2')->outerHtml()
        );

        $this->assertEquals(
            '<p class="p-0 p-2 p-1 p-3">test</p>',
            $hq->find('p')->toggleClass('p-1 p-3')->outerHtml()
        );

        $this->assertEquals(
            '<p>test</p>',
            $hq->find('p')->toggleClass('p-1 p-3 p-0 p-2')->outerHtml()
        );

        $hq->find('p')->addClass('p-0 p-1 p-2');
        $this->assertEquals(
            '<p class="p-0 p-1 p-2 p-3">test</p>',
            $hq->find('p')->toggleClass('p-3 p-2', true)->outerHtml()
        );

        $this->assertEquals(
            '<p class="p-0 p-1 p-2">test</p>',
            $hq->find('p')->toggleClass('p-3 p-4', false)->outerHtml()
        );

        $this->assertEquals(
            '<p class="p-0 p-1 p-2">test</p>',
            $hq->find('p')->toggleClass('p-0 p-1 p-2', true)->outerHtml()
        );

        $this->assertEquals(
            '<p>test</p>',
            $hq->find('p')->toggleClass(null, false)->outerHtml()
        );

        $this->assertEquals(
            '<p class="foo">test</p>',
            $hq->find('p')->toggleClass('foo')->outerHtml()
        );
    }

    public function testCss()
    {
        $html = '
            <div class="content">
                <img src="1.png">
                <img src="2.png">
                <img src="3.png">
            </div>
        ';
        $hq = HQ::html($html);

        $hq->find('img')->css('border', 'none');
        $this->assertHtmlEquals(
            '
            <div class="content">
                <img src="1.png" style="border: none;">
                <img src="2.png" style="border: none;">
                <img src="3.png" style="border: none;">
            </div>',
            $hq->outerHtml()
        );

        $hq->find('img')->css(['width' => '100px', 'height' => '100px']);
        $this->assertHtmlEquals(
            '
            <div class="content">
                <img src="1.png" style="border: none; width: 100px; height: 100px;">
                <img src="2.png" style="border: none; width: 100px; height: 100px;">
                <img src="3.png" style="border: none; width: 100px; height: 100px;">
            </div>',
            $hq->outerHtml()
        );

        $hq->find('img')->setCss('width', '200px');
        $this->assertHtmlEquals(
            '
            <div class="content">
                <img src="1.png" style="border: none; width: 200px; height: 100px;">
                <img src="2.png" style="border: none; width: 200px; height: 100px;">
                <img src="3.png" style="border: none; width: 200px; height: 100px;">
            </div>',
            $hq->outerHtml()
        );

        $this->assertEquals('none', $hq->find('img')->css('border'));
        $this->assertEquals('200px', $hq->find('img')->getCss('width'));
        $this->assertNull($hq->find('img')->getCss('font-size'));

        $hq->find('img')->setCss('border', null);
        $this->assertHtmlEquals(
            '
            <div class="content">
                <img src="1.png" style="width: 200px; height: 100px;">
                <img src="2.png" style="width: 200px; height: 100px;">
                <img src="3.png" style="width: 200px; height: 100px;">
            </div>',
            $hq->outerHtml()
        );

        $hq->find('img')->css(['width' => null]);
        $this->assertHtmlEquals(
            '
            <div class="content">
                <img src="1.png" style="height: 100px;">
                <img src="2.png" style="height: 100px;">
                <img src="3.png" style="height: 100px;">
            </div>',
            $hq->outerHtml()
        );

        $hq->find('img')->removeCss('height');
        $this->assertHtmlEquals(
            '
            <div class="content">
                <img src="1.png" style="">
                <img src="2.png" style="">
                <img src="3.png" style="">
            </div>',
            $hq->outerHtml()
        );

        $this->assertNull($hq->find('.content')->css('none'));

        $hq->find('img')->eq(0)->css('WIDTH', '100px');
        $this->assertEquals('100px', $hq->find('img')->eq(0)->css('width'));

        $hq->find('img')->eq(0)->css('width', '99px');
        $this->assertEquals(
            '<img src="1.png" style="width: 99px;">',
            $hq->find('img')->eq(0)->outerHtml()
        );

        $hq->find('img')->eq(0)->removeCss('WIDTH');
        $this->assertEquals(
            '<img src="1.png" style="">',
            $hq->find('img')->eq(0)->outerHtml()
        );
    }
}
