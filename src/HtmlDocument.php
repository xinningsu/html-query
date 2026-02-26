<?php

namespace Sulao\HtmlQuery;

use Dom\HTMLDocument as DOMDocument;
use Dom\Node as DOMNode;
use DOMException;

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

            $tmp = DomDocument::createFromString(
                $selector,
                LIBXML_NOERROR
            );

            foreach ($tmp->body->childNodes as $node) {
                $frag->appendChild($this->doc->importNode($node, true));
            }

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
        try {
            $nodes = Helper::selectorQuery(
                $selector,
                $this->doc,
                $this->doc
            );
        } catch (DomException $e) {
            $nodes = Helper::xpathQuery(
                Helper::toXpath($selector, namespace: Helper::XPATH_DEFAULT_NAMESPACE),
                $this->doc,
                $this->doc,
                Helper::XPATH_DEFAULT_NAMESPACE
            );
        }

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
