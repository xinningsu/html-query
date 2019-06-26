# html-query
A jQuery-like html processor written in PHP

# Quick Start

### Get contents
```php
<?php

use Sulao\HtmlQuery\HQ;

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

echo $hq('.title')->html();
//this is title

echo $hq('.content')->html();
//this is <b>content</b>...

echo $hq('.content')->outerHtml();
//<div class="content">this is <b>content</b>...</div>

echo $hq('.content')->text();
//this is content...
```

### Get attributes
```php
<?php

use Sulao\HtmlQuery\{HQ, HtmlQuery};

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

$images = $hq('.container img')->map(function (DOMElement $node) {
    return $node->getAttribute('src');
});
print_r($images);
//['1.png', '2.png', '3.png']

// or resolve the DOMElement to HtmlQuery instance by type hinting
$images = $hq('.container img')->map(function (HtmlQuery $node) {
    return $node->attr('src');
});
print_r($images);
//['1.png', '2.png', '3.png']

// Specified which node
echo $hq('.container img')->eq(1)->attr('src');
//2.png

// Or access the DOMNode like array
echo $hq('.container img')[1]->getAttribute('src');
//2.png
```

### Change attributes
```php
<?php

use Sulao\HtmlQuery\{HQ, HtmlQuery};

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
$hq('.container')->outerHtml();
/*
<div class="container">
    <img src="1.png" width="100">
    <img src="2.png" width="100">
    <div class="img">
        <img src="3.png" width="200" data-src="3">
    </div>
</div>
*/

$images->removeAllAttrs(['src']);
echo $hq('.container')->outerHtml();
/*
<div class="container">
    <img src="1.png">
    <img src="2.png">
    <div class="img">
        <img src="3.png">
    </div>
</div>
*/

$images->each(function (DOMElement $node) {
    $node->setAttribute('title', 'html query');
});
// Or resolve to HtmlQuery instance
$images->each(function (HtmlQuery $node, $index) {
    $node->attr(['alt' => 'image ' . ($index + 1)]);
});
echo $hq('.container')->outerHtml();
/*
<div class="container">
    <img src="1.png" alt="image 1" title="html query">
    <img src="2.png" alt="image 2" title="html query">
    <div class="img">
        <img src="3.png" alt="image 3" title="html query">
    </div>
</div>
*/
```

### Change structure
```php
<?php

use Sulao\HtmlQuery\HQ;

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

$hq('.container img')->not('.img img')->wrap('<divclass="img"></div>');
echo $hq('.container')->outerHtml();
// The indentation maybe not the same, but the html structure should be the same. 
/*
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
</div>
*/

$hq('.container img')->unwrap();
echo $hq('.container')->outerHtml();
/*
<div class="container">
    <img src="1.png">
    <img src="2.png">
    <img src="3.png">
</div>
*/

$hq('<img src="0.png"/>')->prependTo('.container');
$hq->find('.container')->append('<img src="4.png"/>');
echo $hq('.container')->outerHtml();
/*
<div class="container">
    <img src="0.png">
    <img src="1.png">
    <img src="2.png">
    <img src="3.png">
    <img src="4.png">
</div>
*/

$hq("img[src='0.png']")->remove();
$hq("img[src='2.png']")->after('<img src="2.5.png"/>');
$hq("img[src='1.png']")->before($hq("img[src='4.png']"));
echo $hq('.container')->outerHtml();
/*
<div class="container">
    <img src="4.png">
    <img src="1.png">
    <img src="2.png">
    <img src="2.5.png">
    <img src="3.png">
</div>
*/
```

# Reference

- [https://www.php.net/manual/en/book.dom.php](https://www.php.net/manual/en/book.dom.php)
- [https://github.com/symfony/css-selector](https://github.com/symfony/css-selector)
- [https://api.jquery.com/](https://api.jquery.com/)

# License

MIT
