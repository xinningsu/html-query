<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\Helper;

class HelperTest extends TestCase
{
    public function testToXpath()
    {
        $xpath = "descendant::*[@class and contains(concat(' ', "
            . "normalize-space(@class), ' '), ' fruit ')]";

        $this->assertEquals(
            $xpath,
            Helper::toXpath('.fruit')
        );
    }

    public function testSplitClass()
    {
        $this->assertEquals(
            ['bar'],
            Helper::splitClass('bar')
        );

        $this->assertEquals(
            ['foo', 'bar'],
            Helper::splitClass('foo bar')
        );
    }

    public function testStrictArrayUnique()
    {
        $arr = [1, '1', '0', 0, 0];
        $this->assertEquals([1, '1', '0', 0], Helper::strictArrayUnique($arr));
    }

    public function testStrictArrayIntersect()
    {
        $arr1 = [1, '0'];
        $arr2 = ['1', 0];

        $this->assertEquals([], Helper::strictArrayIntersect($arr1, $arr2));
        $this->assertEquals([], Helper::strictArrayIntersect($arr1, []));
        $this->assertEquals([], Helper::strictArrayIntersect([], $arr2));

        $arr1 = ['0', 1];
        $arr2 = ['1', 0, 1];
        $this->assertEquals([1], Helper::strictArrayIntersect($arr1, $arr2));

        $arr2 = ['1', '0', 1];
        $this->assertEquals($arr1, Helper::strictArrayIntersect($arr1, $arr2));
    }

    public function testStrictArrayDiff()
    {
        $arr1 = [1, '0'];
        $arr2 = ['1', 0];

        $this->assertEquals([1, '0'], Helper::strictArrayDiff($arr1, $arr2));
        $this->assertEquals([1, '0'], Helper::strictArrayDiff($arr1, []));
        $this->assertEquals([], Helper::strictArrayDiff([], $arr2));

        $arr1 = ['0', 1];
        $arr2 = ['1', 0, 1];
        $this->assertEquals(['0'], Helper::strictArrayDiff($arr1, $arr2));

        $arr2 = ['1', '0', 1];
        $this->assertEquals([], Helper::strictArrayDiff($arr1, $arr2));
    }

    public function testSplitCss()
    {
        $style = 'width: 100px; height: 100px;border: none';
        $this->assertEquals(
            ['width' => '100px', 'height' => '100px', 'border' => 'none'],
            Helper::splitCss($style)
        );
    }

    public function testImplodeCss()
    {
        $css = ['width' => '100px', 'height' => '100px', 'border' => 'none'];
        $this->assertEquals(
            'width: 100px; height: 100px; border: none;',
            Helper::implodeCss($css)
        );
    }

    public function testIsRawHtml()
    {
        $this->assertTrue(Helper::isRawHtml('<p>'));
        $this->assertTrue(Helper::isRawHtml('</div>'));
        $this->assertTrue(Helper::isRawHtml('   <div>ddd</div>'));

        $this->assertFalse(Helper::isRawHtml('#id'));
        $this->assertFalse(Helper::isRawHtml('.class'));
        $this->assertFalse(Helper::isRawHtml('ul > li  p'));
    }

    public function testIsIdSelector()
    {
        $this->assertTrue(Helper::isIdSelector('#id'));
        $this->assertTrue(Helper::isIdSelector('#id2'));
        $this->assertTrue(Helper::isIdSelector('#id-2'));

        $this->assertFalse(Helper::isIdSelector('<p>'));
        $this->assertFalse(Helper::isIdSelector('.class'));
        $this->assertFalse(Helper::isIdSelector('ul > li  p'));
    }
}
