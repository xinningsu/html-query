<?php

namespace Sulao\HtmlQuery;

use DOMDocument, DOMElement, DOMNode, DOMNodeList;
use Traversable;

/**
 * Class HtmlQuery
 *
 * @package Sulao\HtmlQuery
 */
class HtmlQuery extends Selection
{
    const VERSION = '1.0.0';

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
        return $this->mapFirst(function (DOMNode $node) {
            return $this->doc->saveHTML($node);
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
        return $this->mapFirst(function (DOMNode $node) {
            $content = '';
            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $content .= $this->doc->saveHTML($childNode);
            }

            return $content;
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
        return $this->mapFirst(function (DOMNode $node) {
            return $node->textContent;
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
        return $this->each(function (DOMNode $node) use ($text) {
            return $node->nodeValue = $text;
        });
    }

    /**
     * Get the value of an attribute for the first matched node
     * or set one or more attributes for every matched node.
     *
     * @param string|array $name
     * @param string|null  $value
     *
     * @return static|mixed|null
     */
    public function attr($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->setAttr($key, $val);
            }

            return $this;
        }

        if (!is_null($value)) {
            return $this->setAttr($name, $value);
        }

        return $this->getAttr($name);
    }

    /**
     * Get the value of an attribute for the first matched node
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getAttr(string $name)
    {
        return $this->mapFirst(function (DOMNode $node) use ($name) {
            if (!($node instanceof DOMElement)) {
                return null;
            }

            return $node->getAttribute($name);
        });
    }

    /**
     * Set one or more attributes for every matched node.
     *
     * @param string $name
     * @param string $value
     *
     * @return static
     */
    public function setAttr(string $name, string $value)
    {
        return $this->each(function (DOMNode $node) use ($name, $value) {
            if ($node instanceof DOMElement) {
                $node->setAttribute($name, $value);
            }
        });
    }

    /**
     * Remove an attribute from every matched nodes.
     *
     * @param string $attributeName
     *
     * @return static
     */
    public function removeAttr(string $attributeName)
    {
        return $this->each(function (DOMNode $node) use ($attributeName) {
            if ($node instanceof DOMElement) {
                $node->removeAttribute($attributeName);
            }
        });
    }

    /**
     * Remove all attributes from every matched nodes except the specified ones.
     *
     * @param string|array $except The attribute name(s) that won't be removed
     *
     * @return static
     */
    public function removeAllAttrs($except = [])
    {
        return $this->each(function (DOMNode $node) use ($except) {
            $names = [];
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $names[] = $attribute->name;
            }

            foreach (array_diff($names, (array) $except) as $name) {
                if ($node instanceof DOMElement) {
                    $node->removeAttribute($name);
                }
            }
        });
    }

    /**
     * Determine whether any of the nodes have the given attribute.
     *
     * @param string $attributeName
     *
     * @return bool
     */
    public function hasAttr(string $attributeName)
    {
        return $this->mapAnyTrue(
            function (DOMNode $node) use ($attributeName) {
                if (!($node instanceof DOMElement)) {
                    return false;
                }

                return $node->hasAttribute($attributeName);
            }
        );
    }

    /**
     * Alias of attr
     *
     * @param string|array $name
     * @param string|null  $value
     *
     * @return static|mixed|null
     */
    public function prop($name, $value = null)
    {
        return $this->attr($name, $value);
    }

    /**
     * Alias of removeAttr
     *
     * @param string $attributeName
     *
     * @return static
     */
    public function removeProp(string $attributeName)
    {
        return $this->removeAttr($attributeName);
    }

    /**
     * Get the value of an attribute with prefix data- for the first matched
     * node, if the value is valid json string, returns the value encoded in
     * json in appropriate PHP type
     *
     * or set one or more attributes with prefix data- for every matched node.
     *
     * @param string|array $name
     * @param string|null  $value
     *
     * @return static|mixed|null
     */
    public function data($name, $value = null)
    {
        if (is_array($name)) {
            $keys = array_keys($name);
            $keys = array_map(function ($value) {
                return 'data-' . $value;
            }, $keys);

            $name = array_combine($keys, $name);
        } else {
            $name = 'data-' . $name;
        }

        if (!is_null($value) && !is_string($value)) {
            $value = (string) json_encode($value);
        }

        $result = $this->attr($name, $value);

        if (is_string($result)) {
            $json = json_decode($result);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return $result;
    }

    /**
     * Determine whether any of the nodes have the given attribute
     * prefix with data-.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasData(string $name)
    {
        return $this->hasAttr('data-' . $name);
    }

    /**
     * Remove an attribute prefix with data- from every matched nodes.
     *
     * @param string $name
     *
     * @return static
     */
    public function removeData(string $name)
    {
        return $this->removeAttr('data-' . $name);
    }

    /**
     * Remove all child nodes of all matched nodes from the DOM.
     *
     * @return static
     */
    public function empty()
    {
        return $this->each(function (DOMNode $node) {
            $node->nodeValue = '';
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
            $this->each(function (DOMNode $node) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
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
        return $this->mapFirst(function (DOMNode $node) {
            if (!($node instanceof DOMElement)) {
                return null;
            }

            switch ($node->tagName) {
                case 'input':
                    return $node->getAttribute('value');
                case 'textarea':
                    return $node->nodeValue;
                case 'select':
                    $ht = $this->resolve($node);

                    $selected = $ht->children('option:selected');
                    if ($selected->count()) {
                        return $selected->getAttr('value');
                    }

                    $fistChild = $ht->xpathFind('child::*[1]');
                    if ($fistChild->count()) {
                        return $fistChild->getAttr('value');
                    }
                    break;
            }

            return null;
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
        return $this->each(function (DOMNode $node) use ($value) {
            if (!($node instanceof DOMElement)) {
                return;
            }

            switch ($node->tagName) {
                case 'input':
                    $node->setAttribute('value', $value);
                    break;
                case 'textarea':
                    $node->nodeValue = $value;
                    break;
                case 'select':
                    $ht = $this->resolve($node);

                    $selected = $ht->children('option:selected');
                    if ($selected->count()) {
                        $selected->removeAttr('selected');
                    }

                    $options = $ht->children("option[value='{$value}']");
                    if ($options->count()) {
                        $options->first()->setAttr('selected', 'selected');
                    }
                    break;
            }
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
        return $this->each(function (HtmlQuery $node) use ($className) {
            if (!$node->hasAttr('class')) {
                $node->setAttr('class', $className);
                return;
            }

            $classNames = Helper::splitClass($className);
            $class = (string) $node->getAttr('class');
            $classes = Helper::splitClass($class);

            $classArr = array_diff($classNames, $classes);
            if (empty($classArr)) {
                return;
            }

            $class .= ' ' . implode(' ', $classArr);
            $this->setAttr('class', $class);
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
            function (HtmlQuery $node) use ($className) {
                if (!$node->hasAttr('class')) {
                    return false;
                }

                $class = (string) $node->getAttr('class');
                $classes = Helper::splitClass($class);

                return in_array($className, $classes);
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
        return $this->each(function (HtmlQuery $node) use ($className) {
            if (!$node->hasAttr('class')) {
                return;
            }

            if (is_null($className)) {
                $node->removeAttr('class');
                return;
            }

            $classNames = Helper::splitClass($className);
            $class = (string) $node->getAttr('class');
            $classes = Helper::splitClass($class);

            $classArr = array_diff($classes, $classNames);
            if (empty($classArr)) {
                $node->removeAttr('class');
                return;
            }

            $class = implode(' ', $classArr);
            $node->setAttr('class', $class);
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
        return $this->each(function (HtmlQuery $node) use ($className, $state) {
            if (!is_null($state)) {
                if ($state) {
                    $node->addClass($className);
                } else {
                    $node->removeClass($className);
                }
                return;
            }

            if (!$this->hasAttr('class')) {
                $node->setAttr('class', $className);
                return;
            }

            $classNames = Helper::splitClass($className);
            $classes = Helper::splitClass((string) $this->getAttr('class'));

            $classArr = array_diff($classes, $classNames);
            $classArr = array_merge(
                $classArr,
                array_diff($classNames, $classes)
            );
            if (empty($classArr)) {
                $node->removeClass($className);
                return;
            }

            $node->setAttr('class', implode(' ', $classArr));
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
        return $this->mapFirst(function (HtmlQuery $node) use ($name) {
            $style = (string) $node->attr('style');
            $css = Helper::splitCss($style);
            if (!$css) {
                return null;
            }

            if (array_key_exists($name, $css)) {
                return $css[$name];
            }

            $arr = array_change_key_case($css, CASE_LOWER);
            $key = strtolower($name);
            if (array_key_exists($key, $arr)) {
                return $arr[$key];
            }

            return null;
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
        return $this->each(function (HtmlQuery $node) use ($name, $value) {
            if ((string) $value === '') {
                $node->removeCss($name);
                return;
            }

            $style = (string) $node->attr('style');
            if (!$style) {
                $node->setAttr('style', $name . ': ' . $value . ';');
                return;
            }

            $css = Helper::splitCss($style);
            if (!array_key_exists($name, $css)) {
                $allKeys = array_keys($css);
                $arr = array_combine(
                    $allKeys,
                    array_map('strtolower', $allKeys)
                ) ?: [];

                $keys = array_keys($arr, strtolower($name));
                foreach ($keys as $key) {
                    unset($css[$key]);
                }
            }

            $css[$name] = $value;
            $style = Helper::implodeCss($css);
            $this->setAttr('style', $style);
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
        return $this->each(function (HtmlQuery $node) use ($name) {
            $style = (string) $node->attr('style');
            if (!$style) {
                return;
            }

            $css = Helper::splitCss($style);
            $removed = false;
            if (array_key_exists($name, $css)) {
                unset($css[$name]);
                $removed = true;
            } else {
                $allKeys = array_keys($css);
                $arr = array_combine(
                    $allKeys,
                    array_map('strtolower', $allKeys)
                ) ?: [];

                $keys = array_keys($arr, strtolower($name));
                foreach ($keys as $key) {
                    unset($css[$key]);
                    $removed = true;
                }
            }

            if ($removed) {
                $style = Helper::implodeCss($css);
                $this->setAttr('style', $style);
            }
        });
    }

    /**
     * Insert content or node(s) before each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function before($content)
    {
        $content = $this->contentResolve($content);

        return $this->each(function (DOMNode $node, $index) use ($content) {
            $content->each(function (DOMNode $newNode) use ($node, $index) {
                if ($node->parentNode) {
                    $newNode = $index !== $this->count() - 1
                        ? $newNode->cloneNode(true)
                        : $newNode;

                    $node->parentNode->insertBefore($newNode, $node);
                }
            });
        });
    }

    /**
     * Insert every matched node before the target.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function insertBefore($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->before($this);
    }

    /**
     * Insert content or node(s) after each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function after($content)
    {
        $content = $this->contentResolve($content);

        return $this->each(function (HtmlQuery $node, $index) use ($content) {
            $content->each(function (DOMNode $newNode) use ($node, $index) {
                $newNode = $index !== $this->count() - 1
                    ? $newNode->cloneNode(true)
                    : $newNode;

                if ($node->next()->count()) {
                    $node->next()->before($newNode);
                } else {
                    $node->parent()->append($newNode);
                }
            }, true);
        });
    }

    /**
     * Insert every matched node after the target.
     *
     * @param string|DOMNode|DOMNode[]DOMNodeList|static $selector
     *
     * @return static
     */
    public function insertAfter($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->after($this);
    }

    /**
     * Insert content or node(s) to the end of every matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function append($content)
    {
        $content = $this->contentResolve($content);

        return $this->each(function (DOMNode $node, $index) use ($content) {
            $content->each(function (DOMNode $newNode) use ($node, $index) {
                $newNode = $index !== $this->count() - 1
                    ? $newNode->cloneNode(true)
                    : $newNode;

                $node->appendChild($newNode);
            });
        });
    }

    /**
     * Insert every matched node to the end of the target.
     *
     * @param string|DOMNode|DOMNode[]DOMNodeList|static $selector
     *
     * @return static
     */
    public function appendTo($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->append($this);
    }

    /**
     * Insert content or node(s) to the beginning of each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function prepend($content)
    {
        $content = $this->contentResolve($content);

        return $this->each(function (DOMNode $node, $index) use ($content) {
            $content->each(function (DOMNode $newNode) use ($node, $index) {
                $newNode = $index !== $this->count() - 1
                    ? $newNode->cloneNode(true)
                    : $newNode;

                if ($node->firstChild) {
                    $node->insertBefore($newNode, $node->firstChild);
                } else {
                    $node->appendChild($newNode);
                }
            }, true);
        });
    }

    /**
     * Insert every matched node to the beginning of the target.
     *
     * @param string|DOMNode|DOMNode[]DOMNodeList|static $selector
     *
     * @return static
     */
    public function prependTo($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->prepend($this);
    }

    /**
     * Replace each matched node with the provided new content or node(s)
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function replaceWith($content)
    {
        $content = $this->contentResolve($content);
        return $this->each(function (DOMNode $node, $index) use ($content) {
            if (!$node->parentNode) {
                return;
            }

            $len = $content->count();
            $content->each(
                function (DOMNode $newNode) use ($node, $index, $len) {
                    $newNode = $index !== $this->count() - 1
                        ? $newNode->cloneNode(true)
                        : $newNode;

                    if ($len === 1) {
                        $node->parentNode->replaceChild($newNode, $node);
                    } else {
                        $this->resolve($newNode)->insertAfter($node);
                    }
                },
                true
            );

            if ($len !== 1) {
                $node->parentNode->removeChild($node);
            }
        });
    }

    /**
     * Replace each target node with the matched node(s)
     *
     * @param string|DOMNode|DOMNode[]DOMNodeList|static $selector
     *
     * @return static
     */
    public function replaceAll($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->replaceWith($this);
    }

    /**
     * Wrap an HTML structure around each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function wrap($content)
    {
        $content = $this->contentResolve($content);
        $newNode = $content[0];

        if (empty($newNode)) {
            return $this;
        }

        $newNode = $content[0];

        return $this->each(function (DOMNode $node, $index) use ($newNode) {
            $newNode = $index !== $this->count() - 1
                ? $newNode->cloneNode(true)
                : $newNode;

            $nodes = $this->xpathQuery('descendant::*[last()]', $newNode);
            if (!$nodes) {
                throw new Exception('Invalid wrap html format.');
            }

            $deepestNode = end($nodes);
            $node->parentNode->replaceChild($newNode, $node);
            $deepestNode->appendChild($node);
        });
    }

    /**
     * Wrap an HTML structure around the content of each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function wrapInner($content)
    {
        $content = $this->contentResolve($content);
        $newNode = $content[0];

        if (empty($newNode)) {
            return $this;
        }

        return $this->each(function (DOMNode $node, $index) use ($newNode) {
            $newNode = $index !== $this->count() - 1
                ? $newNode->cloneNode(true)
                : $newNode;

            $nodes = $this->xpathQuery('descendant::*[last()]', $newNode);
            if (!$nodes) {
                throw new Exception('Invalid wrap html format.');
            }

            $deepestNode = end($nodes);

            // Can't loop $this->node->childNodes directly to append child,
            // Because childNodes will change once appending child.
            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $deepestNode->appendChild($childNode);
            }

            $node->appendChild($newNode);
        });
    }

    /**
     * Wrap an HTML structure around all matched nodes.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function wrapAll($content)
    {
        $content = $this->contentResolve($content);
        if (!$content->count()) {
            return $this;
        }

        $newNode = $content[0];
        $this->each(function (DOMNode $node, $index) use ($newNode) {
            if ($index === 0) {
                $this->resolve($node)->wrap($newNode);
            } else {
                $this->nodes[0]->parentNode->appendChild($node);
            }
        });

        return $this;
    }

    /**
     * Remove the parents of the matched nodes from the DOM.
     * A optional selector to check the parent node against.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function unwrap(?string $selector = null)
    {
        return $this->parent($selector)->unwrapSelf();
    }

    /**
     * Remove the HTML tag of the matched nodes from the DOM.
     * Leaving the child nodes in their place.
     *
     * @return static
     */
    public function unwrapSelf()
    {
        return $this->each(function (DOMNode $node) {
            if (!$node->parentNode) {
                return;
            }

            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $node->parentNode->insertBefore($childNode, $node);
            }

            $node->parentNode->removeChild($node);
        });
    }

    /**
     * Validate the nodes
     *
     * @param DOMNode|DOMNode[]|DOMNodeList|static $nodes
     *
     * @return DOMNode[]
     * @throws Exception
     */
    protected function validateNodes($nodes)
    {
        if (empty($nodes)) {
            $nodes = [];
        } elseif ($nodes instanceof Traversable) {
            $nodes = iterator_to_array($nodes);
        } elseif ($nodes instanceof DOMNode || !is_array($nodes)) {
            $nodes = [$nodes];
        }

        $nodes = Helper::strictArrayUnique($nodes);
        foreach ($nodes as $node) {
            if (!($node instanceof DOMNode)) {
                throw new Exception(
                    'Expect an instance of DOMNode, '
                        . gettype($node) . ' given.'
                );
            }

            if ((!$node->ownerDocument && $node !== $this->doc)
                || ($node->ownerDocument && $node->ownerDocument !== $this->doc)
            ) {
                throw new Exception(
                    'The DOMNode does not belong to the DOMDocument.'
                );
            }
        }

        return $nodes;
    }
}
