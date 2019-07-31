<?php

namespace Sulao\HtmlQuery;

use DOMDocument, DOMNode, DOMNodeList;

/**
 * Trait Selector
 *
 * @package Sulao\HtmlQuery
 */
trait Resolver
{
    /**
     * @var DOMDocument
     */
    protected $doc;

    /**
     * @var DOMNode[]
     */
    protected $nodes;

    /**
     * Selector constructor.
     *
     * @param DOMDocument                          $doc
     * @param DOMNode|DOMNode[]|DOMNodeList|static $nodes
     *
     * @return static
     */
    abstract public function __construct(DOMDocument $doc, $nodes);

    /**
     * Get the descendants of each matched node, filtered by a selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    abstract public function find($selector);

    /**
     * Resolve DOMNode(s) to a static instance.
     *
     * @param DOMNode|DOMNode[]|DOMNodeList|static $nodes
     *
     * @return static
     */
    protected function resolve($nodes)
    {
        if ($nodes instanceof static) {
            return $nodes;
        }

        return new static($this->doc, $nodes);
    }

    /**
     * If the parameter is a css selector, get the descendants
     * of dom document filtered by the css selector.
     * If the parameter is selection, resolve that selection to static object.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    protected function targetResolve($selector)
    {
        if (is_string($selector)) {
            return $this->resolve($this->doc)->find($selector);
        }

        return $this->resolve($selector);
    }

    /**
     * If the parameter is string, consider it as raw html,
     * then create document fragment for it.
     * If the parameter is selection, resolve that selection to static instance.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    protected function contentResolve($content)
    {
        if (is_string($content)) {
            return $this->htmlResolve($content);
        }

        return $this->resolve($content);
    }

    /**
     * Resolve the html content to static instance.
     *
     * @param string $html
     *
     * @return static
     */
    protected function htmlResolve(string $html)
    {
        $frag = $this->doc->createDocumentFragment();
        $frag->appendXML($html);

        return $this->resolve($frag);
    }

    /**
     * Resolve the nodes under the relation to static instance.
     * up to but not including the node matched by the $until selector.
     *
     * @param string $relation
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $until
     *
     * @return static
     */
    protected function relationResolve(string $relation, ?string $until = null)
    {
        $untilNodes = !is_null($until)
            ? $this->targetResolve($until)->nodes
            : [];

        $nodes = [];
        foreach ($this->nodes as $node) {
            while ($node = Helper::getRelationNode($node, $relation)) {
                if (in_array($node, $untilNodes, true)) {
                    break;
                }

                if (!in_array($node, $nodes, true)) {
                    $nodes[] = $node;
                }
            }
        }

        return $this->resolve($nodes);
    }

    /**
     * Resolve the xpath to static instance.
     *
     * @param string $xpath
     *
     * @return static
     */
    protected function xpathResolve(string $xpath)
    {
        $nodes = [];
        foreach ($this->nodes as $node) {
            $nodes = array_merge($nodes, $this->xpathQuery($xpath, $node));
        }

        $nodes = Helper::strictArrayUnique($nodes);

        return $this->resolve($nodes);
    }

    /**
     * Query xpath to an array of DOMNode
     *
     * @param string       $xpath
     * @param DOMNode|null $node
     *
     * @return DOMNode[]
     */
    protected function xpathQuery(
        string $xpath,
        ?DOMNode $node = null
    ): array {
        return Helper::xpathQuery($xpath, $this->doc, $node);
    }
}
