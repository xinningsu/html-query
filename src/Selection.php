<?php

namespace Sulao\HtmlQuery;

use ArrayAccess, ArrayIterator;
use Closure, Countable;
use DOMDocument, DOMNode;
use IteratorAggregate;

/**
 * Class Selection
 *
 * @package Sulao\HtmlQuery
 */
abstract class Selection implements Countable, IteratorAggregate, ArrayAccess
{
    use Selector;

    /**
     * Return DOMDocument
     *
     * @return DOMDocument
     */
    public function getDoc(): DOMDocument
    {
        return $this->doc;
    }

    /**
     * Return DOMNodes
     *
     * @return DOMNode[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Iterate over the matched nodes, executing the function for each node.
     *
     * @param Closure $function function(DOMNode|HtmlQuery $node, $index)
     * @param bool    $reverse  Iterate over the nodes reversely
     *
     * @return static
     */
    public function each(Closure $function, bool $reverse = false)
    {
        $resolve = $this->shouldResolve($function, 0);

        $nodes = $reverse ? array_reverse($this->nodes, true) : $this->nodes;
        foreach ($nodes as $index => $node) {
            $node = $resolve ? $this->resolve($node) : $node;
            $function($node, $index);
        }

        return $this;
    }

    /**
     * Pass each matched node through a function,
     * producing an array containing the return values.
     *
     * @param Closure $function function($index, DOMNode|HtmlQuery $node)
     *
     * @return array
     */
    public function map(Closure $function)
    {
        $resolve = $this->shouldResolve($function, 0);

        $data = [];
        foreach ($this->nodes as $index => $node) {
            $node = $resolve ? $this->resolve($node) : $node;
            $data[] = $function($node, $index);
        }

        return $data;
    }

    /**
     * Pass each matched node through a function,
     * Break and return true when the function with the first node return true.
     *
     * @param Closure $function function($index, DOMNode|HtmlQuery $node)
     *
     * @return bool
     */
    public function mapAnyTrue(Closure $function)
    {
        $resolve = $this->shouldResolve($function, 0);

        foreach ($this->nodes as $index => $node) {
            $node = $resolve ? $this->resolve($node) : $node;
            if ($function($node, $index)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pass the first matched node through a function,
     * and return the return value of the function.
     *
     * @param Closure $function function(DOMNode|HtmlQuery $node)
     *
     * @return mixed|null
     */
    public function mapFirst(Closure $function)
    {
        if (!$this->count()) {
            return null;
        }

        $resolve = $this->shouldResolve($function, 0);
        $node = $resolve ? $this->resolve($this->nodes[0]) : $this->nodes[0];

        return $function($node);
    }

    /**
     * Reduce the current nodes to the one at the specified index.
     *
     * @param int $index
     *
     * @return static
     */
    public function eq(int $index)
    {
        $node = array_key_exists($index, $this->nodes)
            ? $this->nodes[$index]
            : [];

        return $this->resolve($node);
    }

    /**
     * Reduce the current nodes to the first one.
     *
     * @return static
     */
    public function first()
    {
        return $this->eq(0);
    }

    /**
     * Reduce the current nodes to the final one.
     *
     * @return static
     */
    public function last()
    {
        return $this->eq(count($this->nodes) - 1);
    }


    /**
     * Reduce the matched nodes to a subset specified by a range of indices.
     *
     * @param int      $offset
     * @param int|null $length
     *
     * @return static
     */
    public function slice(int $offset, ?int $length = null)
    {
        return $this->resolve(array_slice($this->nodes, $offset, $length));
    }

    /**
     * Return DOMNodes
     *
     * @return DOMNode[]
     */
    public function toArray(): array
    {
        return $this->getNodes();
    }

    public function unset(int $offset)
    {
        unset($this->nodes[$offset]);
        $this->nodes = array_values($this->nodes);
    }

    public function count(): int
    {
        return count($this->toArray());
    }

    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }

    public function offsetSet($offset, $value)
    {
        if (!($value instanceof DOMNode)) {
            throw new Exception(
                'Expect an instance of DOMNode, '
                    . gettype($value) . ' given.'
            );
        }

        if (!in_array($value, $this->nodes, true)) {
            $this->nodes[] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->nodes[$offset]);
    }

    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }

    public function offsetGet($offset)
    {
        return isset($this->nodes[$offset]) ? $this->nodes[$offset] : null;
    }
}
