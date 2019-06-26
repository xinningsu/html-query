<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Sulao\HtmlQuery\{Exception, Helper, HQ, HtmlQuery};

class HtmlQueryTest extends TestCase
{
    public function testRemove()
    {
        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>Html Query</title>
                <script src="jquery-3.4.1.min.js"></script>
            </head>
            <body>
            <p id="title" class="title"><em>New</em>Html Query</p>
            <script type="application/javascript">
                function zoom() {}
            </script>
            </body>
            </html>
        ';
        $hq = HQ::html($html);
        $hq->find('script')->remove();
        $this->assertHtmlEquals(
            '<!DOCTYPE html>
            <html lang="en">
            <head>
                <title>Html Query</title>
                
            </head>
            <body>
            <p id="title" class="title"><em>New</em>Html Query</p>
            
            </body>
            </html>',
            $hq->outerHtml()
        );

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

        $hq->find('p')->remove('.p-0');
        $this->assertHtmlEquals(
            '
            <img src="1.png">
            <img src="2.png">
            <img src="3.png">
            
            <p>test2</p>',
            $hq->find('.content')->html()
        );
    }

    public function testValidateNodes()
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

        $validateNodes = $this->protectMethod($hq, 'validateNodes');

        $this->assertEquals([], $validateNodes([]));

        $nodes = $this->protectMethod($hq, 'xpathQuery')(
            Helper::toXpath('.fruit')
        );
        $this->assertEquals($nodes, $validateNodes($nodes));
        $this->assertEquals(
            $nodes,
            $validateNodes(array_merge($nodes, $nodes))
        );

        $exception = null;
        try {
            $validateNodes([1]);
        } catch (Exception $exception) {
        }

        $this->assertTrue($exception instanceof Exception);
        $this->assertEquals(
            'Expect an instance of DOMNode, integer given.',
            $exception->getMessage()
        );

        $doc2 = new DOMDocument();
        $doc2->loadHTML($html);
        $hq2 = new HtmlQuery($doc2, []);
        $nodes2 = $this->protectMethod($hq2, 'xpathQuery')(
            Helper::toXpath('.fruit')
        );

        $exception = null;
        try {
            $validateNodes($nodes2);
        } catch (Exception $exception) {
        }

        $this->assertTrue($exception instanceof Exception);
        $this->assertEquals(
            'The DOMNode does not belong to the DOMDocument.',
            $exception->getMessage()
        );
    }
}
