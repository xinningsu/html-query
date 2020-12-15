<?php

namespace Sulao\HtmlQuery;

use DOMDocument;
use DOMNode;

/**
 * Class HtmlDocument
 *
 * @package Sulao\HtmlQuery
 */
class HtmlDocument
{
    /**
     * @var DOMDocument
     */
    protected $doc;

    /**
     * HtmlDocument constructor.
     *
     * @param DOMDocument $doc
     */
    public function __construct(DOMDocument $doc)
    {
        $this->doc = $doc;
    }

    /**
     * Get DOMDocument
     *
     * @return DOMDocument
     */
    public function getDoc(): DOMDocument
    {
        return $this->doc;
    }

    /**
     * Get the outer HTML content.
     *
     * @return string|null
     */
    public function outerHtml()
    {
        return $this->doc->saveHTML();
    }

    /**
     *  Make the static object can be called as a function.
     *
     * @param string $selector
     *
     * @return HtmlQuery
     */
    public function __invoke(string $selector)
    {
        return $this->query($selector);
    }

    /**
     * If the parameter is raw html, then create document fragment for it,
     * If the parameter is a css selector, get the descendants
     * filtered by a css selector.
     *
     * @param string $selector css selector or raw html
     *
     * @return HtmlQuery
     */
    public function query(string $selector)
    {
        if (Helper::isRawHtml($selector)) {
            $frag = $this->doc->createDocumentFragment();
            $frag->appendXML($selector);

            return $this->resolve($frag);
        }

        return $this->find($selector);
    }

    /**
     * Get the descendants of document, filtered by a selector.
     *
     * @param string $selector
     *
     * @return HtmlQuery
     */
    public function find(string $selector)
    {
        $nodes = Helper::xpathQuery(
            Helper::toXpath($selector),
            $this->doc,
            $this->doc
        );

        if (Helper::isIdSelector($selector)) {
            $nodes = $nodes ? $nodes[0] : [];
        }

        return $this->resolve($nodes);
    }

    /**
     * Resolve nodes to HtmlQuery instance.
     *
     * @param DOMNode|DOMNode[] $nodes
     *
     * @return HtmlQuery
     */
    protected function resolve($nodes)
    {
        return new HtmlQuery($this->doc, $nodes);
    }
}
