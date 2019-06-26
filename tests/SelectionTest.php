<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\{HQ, HtmlQuery};

class SelectionTest extends TestCase
{
    public function testGetProperty()
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
        $images = $hq->find('img');

        $this->assertInstanceOf(DOMDocument::class, $images->getDoc());
        $this->assertIsArray($images->getNodes());
        $this->assertInstanceOf(DOMNode::class, $images->getNodes()[0]);
    }

    public function testCount()
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
        $this->assertCount(3, $hq->find('img'));
        $this->assertEquals(3, $hq->find('img')->count());
    }

    public function testArrayAccess()
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
        $images = $hq->find('img');

        $this->assertIsArray($images->toArray());
        $this->assertInstanceOf(DOMNode::class, $images->toArray()[0]);

        $this->assertTrue(isset($images[0]));
        $this->assertFalse(isset($images[3]));

        $this->assertInstanceOf(DOMNode::class, $images[0]);
        $this->assertInstanceOf(DOMNode::class, $images[1]);

        unset($images[0]);
        $this->assertCount(2, $images);
        $this->assertEquals('2.png', $images->attr('src'));

        $images[] = $hq("img[src='1.png']")[0];
        $this->assertCount(3, $images);
        $this->assertEquals('1.png', $images->last()->attr('src'));
    }

    public function testIterator()
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
        $images = $hq->find('img');

        $this->assertInstanceOf(Traversable::class, $images->getIterator());
        foreach ($images as $image) {
            $this->assertInstanceOf(DOMNode::class, $image);
        }
    }

    public function testMap()
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
        $images = $hq->find('img');

        $this->assertEquals(
            $images->map(function ($node) use ($hq) {
                return ($this->protectMethod($hq, 'resolve')(
                    $node
                ))->attr('src');
            }),
            ['1.png', '2.png', '3.png']
        );

        $this->assertTrue($images->mapAnyTrue(function (HtmlQuery $node) {
            return $node->attr('src') === '2.png';
        }));
        $this->assertFalse($images->mapAnyTrue(function (HtmlQuery $node) {
            return $node->attr('src') === '4.png';
        }));

        $this->assertEquals(
            '1.png',
            $images->mapFirst(function (DOMElement $node) {
                return $node->getAttribute('src');
            })
        );
    }

    public function testEach()
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
        $hq->find('img')->each(function (HtmlQuery $node, $index) {
            return $node->attr('alt', 'image alt ' . ($index + 1));
        });

        $this->assertHtmlEquals(
            '<div class="content">
                <img src="1.png" alt="image alt 1">
                <img src="2.png" alt="image alt 2">
                <img src="3.png" alt="image alt 3">
                <p class="p-0">test</p>
                <p>test2</p>
            </div>',
            $hq->find('.content')->outerHtml()
        );
    }

    public function testEq()
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
            'PHP',
            $hq->query('option')->eq(0)->html()
        );

        $this->assertEquals(
            'Dom',
            $hq->query('option')->eq(1)->html()
        );

        $this->assertEquals(
            'jQuery',
            $hq->query('option')->eq(2)->html()
        );

        $this->assertEquals(
            'js',
            $hq->query('option')->eq(3)->html()
        );

        $this->assertEquals(
            null,
            $hq->query('option')->eq(4)->html()
        );
    }

    public function testFirst()
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
            'PHP',
            $hq->query('option')->first()->html()
        );

        $this->assertEquals(
            'PHP',
            $hq->query('option')->first()->first()->html()
        );

        $this->assertEquals(
            null,
            $hq->query('options')->first()->html()
        );
    }

    public function testLast()
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
            'js',
            $hq->query('option')->last()->html()
        );

        $this->assertEquals(
            'js',
            $hq->query('option')->last()->last()->html()
        );

        $this->assertEquals(
            null,
            $hq->query('options')->last()->html()
        );
    }

    public function testSlice()
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
        $hq = HQ::html($html)->query('option');

        $slice = $hq->slice(0, 2);
        $this->assertEquals(2, $slice->count());
        $this->assertEquals('PHP', $slice->html());

        $slice = $hq->slice(1);
        $this->assertEquals(3, $slice->count());
        $this->assertEquals('js', $slice->eq(2)->html());

        $slice = $hq->slice(-2);
        $this->assertEquals(2, $slice->count());
        $this->assertEquals('jQuery', $slice->eq(0)->html());

        $slice = $hq->slice(10);
        $this->assertEquals(0, $slice->count());
    }
}
