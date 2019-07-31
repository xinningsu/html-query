<?php

namespace Sulao\HtmlQuery;

use DOMDocument, DOMNode;

/**
 * Class HtmlNode
 *
 * @package Sulao\HtmlQuery
 */
class HtmlNode
{
    /**
     * @var DOMNode
     */
    protected $node;

    /**
     * HtmlNode constructor.
     *
     * @param DOMNode $node
     */
    public function __construct(DOMNode $node)
    {
        $this->node = $node;
    }

    /**
     * Get the outer HTML content.
     *
     * @return string|null
     */
    public function outerHtml()
    {
        return $this->getDoc()->saveHTML($this->node);
    }

    /**
     * Get the inner HTML content.
     *
     * @return string|null
     */
    public function getHtml()
    {
        $content = '';
        foreach (iterator_to_array($this->node->childNodes) as $childNode) {
            $content .= $this->getDoc()->saveHTML($childNode);
        }

        return $content;
    }

    /**
     * Get the combined text contents, including it's descendants.
     *
     * @return string|null
     */
    public function getText()
    {
        return $this->node->textContent;
    }

    /**
     * Set the text contents.
     *
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->node->nodeValue = $text;
    }

    /**
     * Remove all child nodes from the DOM.
     */
    public function empty()
    {
        $this->node->nodeValue = '';
    }

    /**
     * Remove the node from the DOM.
     */
    public function remove()
    {
        if ($this->node->parentNode) {
            $this->node->parentNode->removeChild($this->node);
        }
    }

    /**
     * Insert a node before the node.
     *
     * @param DOMNode $newNode
     */
    public function before(DOMNode $newNode)
    {
        if ($this->node->parentNode) {
            $this->node->parentNode->insertBefore($newNode, $this->node);
        }
    }

    /**
     * Insert new node after the node.
     *
     * @param DOMNode $newNode
     */
    public function after(DOMNode $newNode)
    {
        $nextSibling = $this->node->nextSibling;

        if ($nextSibling && $nextSibling->parentNode) {
            $nextSibling->parentNode->insertBefore($newNode, $nextSibling);
        } elseif ($this->node->parentNode) {
            $this->node->parentNode->appendChild($newNode);
        }
    }

    /**
     * Insert a node to the end of the node.
     *
     * @param DOMNode $newNode
     */
    public function append(DOMNode $newNode)
    {
        $this->node->appendChild($newNode);
    }

    /**
     * Insert content or node(s) to the beginning of each matched node.
     *
     * @param DOMNode $newNode
     */
    public function prepend(DOMNode $newNode)
    {
        if ($this->node->firstChild) {
            $this->node->insertBefore($newNode, $this->node->firstChild);
        } else {
            $this->node->appendChild($newNode);
        }
    }

    /**
     * Replace the node with the provided node
     *
     * @param DOMNode $newNode
     */
    public function replaceWith(DOMNode $newNode)
    {
        if ($this->node->parentNode) {
            $this->node->parentNode->replaceChild($newNode, $this->node);
        }
    }

    /**
     * Remove the HTML tag of the node from the DOM.
     * Leaving the child nodes in their place.
     */
    public function unwrapSelf()
    {
        foreach (iterator_to_array($this->node->childNodes) as $childNode) {
            $this->before($childNode);
        }

        $this->remove();
    }

    /**
     * Get DOMDocument of the node
     *
     * @return DOMDocument
     */
    protected function getDoc(): DOMDocument
    {
        return $this->node instanceof DOMDocument
            ? $this->node
            : $this->node->ownerDocument;
    }
}
