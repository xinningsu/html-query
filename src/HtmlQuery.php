<?php

namespace Sulao\HtmlQuery;

use Dom\HTMLDocument as DOMDocument;
use Dom\Node as DOMNode;
use Dom\NodeList as DOMNodeList;
use Traversable;

/**
 * Class HtmlQuery
 *
 * @package Sulao\HtmlQuery
 */
class HtmlQuery extends HtmlQueryNode
{
    const VERSION = '1.0.1';

    /**
     * @var DOMDocument
     */
    protected $doc;

    /**
     * @var DOMNode[]
     */
    protected $nodes;

    /**
     * HtmlQuery constructor.
     *
     * @param DOMDocument                   $doc
     * @param DOMNode|DOMNode[]|DOMNodeList $nodes
     *
     * @throws Exception
     */
    public function __construct(DOMDocument $doc, $nodes)
    {
        $this->doc = $doc;
        $this->nodes = $this->validateNodes($nodes);
    }

    /**
     * Get the outer HTML contents of the first matched node.
     *
     * @return string|null
     */
    public function outerHtml()
    {
        return $this->mapFirst(function (HtmlNode $node) {
            return $node->outerHtml();
        });
    }

    /**
     * Get the inner HTML contents of the first matched node or
     * set the inner HTML contents of every matched node.
     *
     * @param string|null $html
     *
     * @return string|null|static
     */
    public function html(?string $html = null)
    {
        if (!is_null($html)) {
            return $this->setHtml($html);
        }

        return $this->getHtml();
    }

    /**
     * Get the inner HTML contents of the first matched node.
     *
     * @return string|null
     */
    public function getHtml()
    {
        return $this->mapFirst(function (HtmlNode $node) {
            return $node->getHtml();
        });
    }

    /**
     * Set the inner HTML contents of every matched node.
     *
     * @param string $html
     *
     * @return static
     */
    public function setHtml(string $html)
    {
        $this->empty();

        if ($html !== '') {
            $this->append($html);
        }

        return $this;
    }

    /**
     * Get the combined text contents of the first matched node, including
     * it's descendants, or set the text contents of every matched node.
     *
     * @param string|null $text
     *
     * @return string|null|static
     */
    public function text(?string $text = null)
    {
        if (!is_null($text)) {
            return $this->setText($text);
        }

        return $this->getText();
    }

    /**
     * Get the combined text contents of the first matched node,
     * including it's descendants.
     *
     * @return string|null
     */
    public function getText()
    {
        return $this->mapFirst(function (HtmlNode $node) {
            return $node->getText();
        });
    }

    /**
     * set the text contents of every matched node.
     *
     * @param string $text
     *
     * @return static
     */
    public function setText(string $text)
    {
        return $this->each(function (HtmlNode $node) use ($text) {
            $node->setText($text);
        });
    }

    /**
     * Remove all child nodes of all matched nodes from the DOM.
     *
     * @return static
     */
    public function empty()
    {
        return $this->each(function (HtmlNode $node) {
            $node->empty();
        });
    }

    /**
     * Remove the matched nodes from the DOM.
     * optionally filtered by a selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function remove(?string $selector = null)
    {
        if (!is_null($selector)) {
            $this->filter($selector)->remove();
        } else {
            $this->each(function (HtmlNode $node) {
                $node->remove();
            });
        }

        return $this;
    }

    /**
     * Get the current value of the first matched node
     * or set the value of every matched node.
     *
     * @param string|null $value
     *
     * @return string|null|static
     */
    public function val(?string $value = null)
    {
        if (is_null($value)) {
            return $this->getVal();
        }

        return $this->setVal($value);
    }

    /**
     * Get the current value of the first matched node
     *
     * @return string|null
     */
    public function getVal()
    {
        return $this->mapFirst(function (HtmlElement $node) {
            return $node->getVal();
        });
    }

    /**
     * Set the value of every matched node.
     *
     * @param string $value
     *
     * @return static
     */
    public function setVal(string $value)
    {
        return $this->each(function (HtmlElement $node) use ($value) {
            $node->setVal($value);
        });
    }

    /**
     * Adds the specified class(es) to each node in the matched nodes.
     *
     * @param string $className
     *
     * @return static
     */
    public function addClass(string $className)
    {
        return $this->each(function (HtmlElement $node) use ($className) {
            $node->addClass($className);
        });
    }

    /**
     * Determine whether any of the matched nodes are assigned the given class.
     *
     * @param string $className
     *
     * @return bool
     */
    public function hasClass(string $className)
    {
        return $this->mapAnyTrue(
            function (HtmlElement $node) use ($className) {
                return $node->hasClass($className);
            }
        );
    }

    /**
     * Remove a single class, multiple classes, or all classes
     * from each matched node.
     *
     * @param string|null $className
     *
     * @return static
     */
    public function removeClass(?string $className = null)
    {
        return $this->each(function (HtmlElement $node) use ($className) {
            $node->removeClass($className);
        });
    }

    /**
     * Add or remove class(es) from each matched node, depending on
     * either the class's presence or the value of the state argument.
     *
     * @param string $className
     * @param bool|null   $state
     *
     * @return static
     */
    public function toggleClass(string $className, ?bool $state = null)
    {
        return $this->each(function (HtmlElement $node) use ($className, $state) {
            $node->toggleClass($className, $state);
        });
    }

    /**
     * Get the value of a computed style property for the first matched node
     * or set one or more CSS properties for every matched node.
     *
     * @param string|array $name
     * @param string|null  $value
     *
     * @return static|string|null
     */
    public function css($name, $value = null)
    {
        if (is_null($value) && !is_array($name)) {
            return $this->getCss($name);
        }

        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->setCss($key, $val);
            }
        } else {
            $this->setCss($name, $value);
        }

        return $this;
    }

    /**
     * Get the value of a computed style property for the first matched node
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getCss(string $name)
    {
        return $this->mapFirst(function (HtmlElement $node) use ($name) {
            return $node->getCss($name);
        });
    }

    /**
     * Set or Remove one CSS property for every matched node.
     *
     * @param string      $name
     * @param string|null $value
     *
     * @return static
     */
    public function setCss(string $name, ?string $value)
    {
        return $this->each(function (HtmlElement $node) use ($name, $value) {
            $node->setCss($name, $value);
        });
    }

    /**
     * Remove one CSS property for every matched node.
     *
     * @param string $name
     *
     * @return static
     */
    public function removeCss(string $name)
    {
        return $this->each(function (HtmlElement $node) use ($name) {
            $node->removeCss($name);
        });
    }

    /**
     * Validate the nodes
     *
     * @param DOMNode|DOMNode[]|DOMNodeList|static $nodes
     *
     * @return DOMNode[]
     */
    protected function validateNodes($nodes)
    {
        $nodes = $this->convertNodes($nodes);

        array_map(function ($node) {
            if (!($node instanceof DOMNode)) {
                throw new Exception(
                    'Expect an instance of DOMNode, '
                        . gettype($node) . ' given.'
                );
            }

            $document = $node->ownerDocument ?: $node;

            if ($document !== $this->doc) {
                throw new Exception(
                    'The DOMNode does not belong to the DOMDocument.'
                );
            }
        }, $nodes);

        return $nodes;
    }

    /**
     * Convert nodes to array
     *
     * @param DOMNode|DOMNode[]|DOMNodeList|static $nodes
     *
     * @return array
     */
    protected function convertNodes($nodes): array
    {
        if (empty($nodes)) {
            $nodes = [];
        } elseif ($nodes instanceof Traversable) {
            $nodes = iterator_to_array($nodes);
        } elseif ($nodes instanceof DOMNode || !is_array($nodes)) {
            $nodes = [$nodes];
        }

        return Helper::strictArrayUnique($nodes);
    }
}
