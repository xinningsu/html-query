<?php

namespace Sulao\HtmlQuery;

use Dom\Element as DOMElement;

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
        switch (strtolower($this->node->tagName)) {
            case 'input':
                return $this->node->getAttribute('value');
            case 'textarea':
                return $this->node->textContent;
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
        switch (strtolower($this->node->tagName)) {
            case 'input':
                $this->node->setAttribute('value', $value);
                break;
            case 'textarea':
                $doc = $this->node->ownerDocument;
                $this->node->replaceChildren(
                    $doc->createTextNode($value)
                );
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
        if (strtolower($this->node->tagName) == 'select') {
            foreach ($this->node->querySelectorAll('option') as $option) {
                if ($option->getAttribute('value') === $value) {
                    $option->setAttribute('selected', '');
                } else {
                    $option->removeAttribute('selected');
                }
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
        $option = $this->node->querySelector('option[selected]')
            ?? $this->node->querySelector('option');

        if (!$option) {
            return null;
        }

        return $option->getAttribute('value');
    }
}
