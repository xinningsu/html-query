<?php

namespace Sulao\HtmlQuery;

/**
 * Class HtmlQueryAttribute
 *
 * @package Sulao\HtmlQuery
 */
abstract class HtmlQueryAttribute extends Selection
{
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
        return $this->mapFirst(function (HtmlElement $node) use ($name) {
            return $node->getAttr($name);
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
        return $this->each(function (HtmlElement $node) use ($name, $value) {
            $node->setAttr($name, $value);
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
        return $this->each(function (HtmlElement $node) use ($attributeName) {
            $node->removeAttr($attributeName);
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
        return $this->each(function (HtmlElement $node) use ($except) {
            $node->removeAllAttrs($except);
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
            function (HtmlElement $node) use ($attributeName) {
                return $node->hasAttr($attributeName);
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
     * @param string|array|null  $value
     *
     * @return static|mixed|null
     */
    public function data($name, $value = null)
    {
        if (is_array($name)) {
            array_walk($name, function ($val, $key) {
                $this->data($key, $val);
            });

            return $this;
        }

        $name = 'data-' . $name;

        if (is_null($value)) {
            $result = $this->getAttr($name);

            $json = json_decode($result);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }

            return $result;
        }

        if (is_array($value)) {
            $value = (string) json_encode($value);
        }

        return $this->setAttr($name, $value);
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
}
