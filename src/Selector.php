<?php

namespace Sulao\HtmlQuery;

use Closure;
use DOMDocument, DOMNode, DOMNodeList, DOMXPath;
use ReflectionException, ReflectionFunction;

/**
 * Trait Selector
 *
 * @package Sulao\HtmlQuery
 */
trait Selector
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
     *  Make the static object can be called as a function.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static|null
     */
    public function __invoke($selector)
    {
        if (is_string($selector)) {
            return $this->query($selector);
        }

        return $this->resolve($selector);
    }

    /**
     * If the parameter is raw html, then create document fragment for it,
     * If the parameter is a css selector, get the descendants
     * of each current node, filtered by a css selector.
     * If the parameter is selection, resolve that selection
     *
     * @param string $selector css selector or raw html
     *
     * @return static
     */
    public function query(string $selector)
    {
        if (Helper::isRawHtml($selector)) {
            return $this->htmlResolve($selector);
        }

        return $this->targetResolve($selector);
    }

    /**
     * Get the descendants of each matched node, filtered by a selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function find($selector)
    {
        if (is_string($selector)) {
            $selection = $this->xpathFind(Helper::toXpath($selector));

            return Helper::isIdSelector($selector)
                ? $selection->first()
                : $selection;
        }

        $descendants = $this->xpathFind('descendant::*');

        return $descendants->intersect($selector);
    }

    /**
     * Get the nodes in the current nodes filtered by a selector.
     *
     * @param string|Closure|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function filter($selector)
    {
        if (is_string($selector)) {
            $xpath = Helper::toXpath($selector, 'self::');
            return $this->xpathFind($xpath);
        } elseif ($selector instanceof Closure) {
            $resolve = $this->shouldResolve($selector, 1);

            $nodes = [];
            foreach ($this->nodes as $key => $node) {
                if ($selector($key, $resolve ? $this->resolve($node) : $node)) {
                    $nodes[] = $node;
                }
            }

            return $this->resolve($nodes);
        }

        return $this->intersect($selector);
    }

    /**
     * Get the parent of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function parent(?string $selector = null)
    {
        $selector = is_null($selector) ? '*' : $selector;
        $xpath = Helper::toXpath($selector, 'parent::');

        return $this->xpathFind($xpath);
    }

    /**
     * Get the ancestors of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function parents(?string $selector = null)
    {
        $selector = is_null($selector) ? '*' : $selector;
        $xpath = Helper::toXpath($selector, 'ancestor::');

        return $this->xpathFind($xpath);
    }

    /**
     * Get the ancestors of each node in the current nodes,
     * up to but not including the node matched by the selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function parentsUntil($selector)
    {
        return $this->relationResolve('parentNode', $selector);
    }

    /**
     * Get the children of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function children(?string $selector = null)
    {
        $selector = is_null($selector) ? '*' : $selector;
        $xpath = Helper::toXpath($selector, 'child::');

        return $this->xpathFind($xpath);
    }

    /**
     * Get the siblings of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function siblings(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "preceding-sibling::{$xpath}|following-sibling::{$xpath}";

        return $this->xpathFind($xpath);
    }

    /**
     * Get the immediately preceding sibling of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function prev(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "preceding-sibling::{$xpath}[1]";

        return $this->xpathFind($xpath);
    }

    /**
     * Get all preceding siblings of each node in the current nodes,
     * optionally filtered by a css selector
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function prevAll(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "preceding-sibling::{$xpath}";

        return $this->xpathFind($xpath);
    }

    /**
     * Get all preceding siblings of each node
     * up to but not including the node matched by the selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function prevUntil($selector)
    {
        return $this->relationResolve('previousSibling', $selector);
    }

    /**
     * Get the immediately following sibling of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function next(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "following-sibling::{$xpath}[1]";

        return $this->xpathFind($xpath);
    }

    /**
     * Get all following siblings of each node in the current nodes,
     * optionally filtered by a selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function nextAll(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "following-sibling::{$xpath}";

        return $this->xpathFind($xpath);
    }

    /**
     * Get all following siblings of each node
     * up to but not including the node matched by the selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function nextUntil($selector)
    {
        return $this->relationResolve('nextSibling', $selector);
    }

    /**
     * Create a new static object with nodes added to the current nodes.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function add($selector)
    {
        $nodes = $this->targetResolve($selector)->toArray();

        $nodes = array_merge($this->nodes, $nodes);
        $nodes = Helper::strictArrayUnique($nodes);

        return $this->resolve($nodes);
    }

    /**
     * Create a new static object with the intersected nodes
     * between current nodes and nodes of the selection.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function intersect($selector)
    {
        $selection = $this->targetResolve($selector);

        return $this->resolve(
            Helper::strictArrayIntersect($this->nodes, $selection->nodes)
        );
    }

    /**
     * Remove nodes from the current nodes.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function not($selector)
    {
        $nodes = $this->targetResolve($selector)->toArray();
        $nodes = Helper::strictArrayDiff($this->nodes, $nodes);

        return $this->resolve($nodes);
    }

    /**
     * Check the current matched nodes against a selector, node(s), or static
     * object and return true if at least one of nodes matches.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return bool
     */
    public function is($selector): bool
    {
        if ($this->count()) {
            return (bool) $this->intersect($selector)->count();
        }

        return false;
    }

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
        $until = !is_null($until)
            ? $this->targetResolve($until)->toArray()
            : null;

        $nodes = [];
        foreach ($this->nodes as $node) {
            while (($node = $node->$relation)
                && $node->nodeType !== XML_DOCUMENT_NODE
            ) {
                if ($node->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }

                if (!is_null($until) && $this->resolve($node)->is($until)) {
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
     * Determine where the parameter of the closure should resolve to static,
     * or just leave it as DOMNode
     *
     * @param Closure $function
     * @param int     $index    Which parameter of the closure, starts with 0
     *
     * @return bool
     */
    protected function shouldResolve(Closure $function, $index = 0)
    {
        try {
            $reflection = new ReflectionFunction($function);
        } catch (ReflectionException $exception) {
            return false;
        }

        $parameters = $reflection->getParameters();
        if ($parameters && array_key_exists($index, $parameters)) {
            $class = $parameters[$index]->getClass();
            if ($class && $class->isInstance($this)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the xpath to static instance.
     *
     * @param string $xpath
     *
     * @return static
     */
    protected function xpathFind(string $xpath)
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
        $docXpath = new DOMXpath($this->doc);
        $nodeList = $docXpath->query($xpath, $node);

        if (!($nodeList instanceof DOMNodeList)) {
            return [];
        }

        return iterator_to_array($nodeList);
    }
}
