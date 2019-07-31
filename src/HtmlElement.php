<?php

namespace Sulao\HtmlQuery;

use DOMElement;

/**
 * Class HtmlElement
 *
 * @package Sulao\HtmlQuery
 */
class HtmlElement extends HtmlNode
{
    use HtmlElementCss;

    /**
     * @var DOMElement
     */
    protected $node;

    /**
     * HtmlElement constructor.
     *
     * @param DOMElement $node
     */
    public function __construct(DOMElement $node)
    {
        $this->node = $node;
    }

    /**
     * Get the value of an attribute
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getAttr(string $name)
    {
        return $this->node->getAttribute($name);
    }

    /**
     * Set attribute.
     *
     * @param string $name
     * @param string $value
     */
    public function setAttr(string $name, string $value)
    {
        $this->node->setAttribute($name, $value);
    }

    /**
     * Remove an attribute.
     *
     * @param string $attributeName
     */
    public function removeAttr(string $attributeName)
    {
        $this->node->removeAttribute($attributeName);
    }

    /**
     * Remove all attributes except the specified ones.
     *
     * @param string|array $except The attribute name(s) that won't be removed
     */
    public function removeAllAttrs($except = [])
    {
        $names = [];
        foreach (iterator_to_array($this->node->attributes) as $attribute) {
            $names[] = $attribute->name;
        }

        foreach (array_diff($names, (array) $except) as $name) {
            $this->node->removeAttribute($name);
        }
    }

    /**
     * Determine whether the node has the given attribute.
     *
     * @param string $attributeName
     *
     * @return bool
     */
    public function hasAttr(string $attributeName)
    {
        return $this->node->hasAttribute($attributeName);
    }

    /**
     * Get the current value of the node.
     *
     * @return string|null
     */
    public function getVal()
    {
        switch ($this->node->tagName) {
            case 'input':
                return $this->node->getAttribute('value');
            case 'textarea':
                return $this->node->nodeValue;
            case 'select':
                return $this->getSelectVal();
        }

        return null;
    }

    /**
     * Set the value of the node.
     *
     * @param string $value
     */
    public function setVal(string $value)
    {
        switch ($this->node->tagName) {
            case 'input':
                $this->node->setAttribute('value', $value);
                break;
            case 'textarea':
                $this->node->nodeValue = $value;
                break;
            case 'select':
                $this->setSelectVal($value);
                break;
        }
    }

    /**
     * Set select hag value
     *
     * @param string $value
     */
    protected function setSelectVal(string $value)
    {
        if ($this->node->tagName == 'select') {
            $nodes = Helper::xpathQuery(
                Helper::toXpath('option:selected', 'child::'),
                $this->getDoc(),
                $this->node
            );

            foreach ($nodes as $node) {
                $node->removeAttribute('selected');
            }

            $nodes = Helper::xpathQuery(
                Helper::toXpath("option[value='{$value}']", 'child::'),
                $this->getDoc(),
                $this->node
            );

            if (count($nodes)) {
                $nodes[0]->setAttribute('selected', 'selected');
            }
        }
    }

    /**
     * Get select tag value
     *
     * @return string|null
     */
    protected function getSelectVal()
    {
        if ($this->node->tagName === 'select') {
            $xpaths = [
                Helper::toXpath('option:selected', 'child::'),
                'child::option[1]'
            ];

            foreach ($xpaths as $xpath) {
                $nodes = Helper::xpathQuery(
                    $xpath,
                    $this->getDoc(),
                    $this->node
                );

                if (count($nodes)) {
                    return $nodes[0]->getAttribute('value');
                }
            }
        }

        return null;
    }
}
