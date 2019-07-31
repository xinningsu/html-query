<?php

namespace Sulao\HtmlQuery;

/**
 * Class HtmlElementCss
 *
 * @package Sulao\HtmlQuery
 */
trait HtmlElementCss
{
    abstract public function hasAttr(string $attributeName);
    abstract public function setAttr(string $name, string $value);
    abstract public function getAttr(string $name);
    abstract public function removeAttr(string $attributeName);

    /**
     * Adds the specified class(es).
     *
     * @param string $className
     */
    public function addClass(string $className)
    {
        if (!$this->hasAttr('class')) {
            $this->setAttr('class', $className);
            return;
        }

        $classNames = Helper::splitClass($className);
        $class = (string) $this->getAttr('class');
        $classes = Helper::splitClass($class);

        $classArr = array_diff($classNames, $classes);
        if (empty($classArr)) {
            return;
        }

        $class .= ' ' . implode(' ', $classArr);
        $this->setAttr('class', $class);
    }

    /**
     * Determine whether the node is assigned the given class.
     *
     * @param string $className
     *
     * @return bool
     */
    public function hasClass(string $className)
    {
        $class = (string) $this->getAttr('class');
        $classes = Helper::splitClass($class);

        return in_array($className, $classes);
    }

    /**
     * Remove a single class, multiple classes, or all classes.
     *
     * @param string|null $className
     */
    public function removeClass(?string $className = null)
    {
        if (!$this->hasAttr('class')) {
            return;
        }

        if (is_null($className)) {
            $this->removeAttr('class');
            return;
        }

        $classNames = Helper::splitClass($className);
        $class = (string) $this->getAttr('class');
        $classes = Helper::splitClass($class);

        $classArr = array_diff($classes, $classNames);
        if (empty($classArr)) {
            $this->removeAttr('class');
            return;
        }

        $class = implode(' ', $classArr);
        $this->setAttr('class', $class);
    }

    /**
     * Add or remove class(es), depending on either the class's presence
     * or the value of the state argument.
     *
     * @param string $className
     * @param bool|null   $state
     */
    public function toggleClass(string $className, ?bool $state = null)
    {
        if (!is_null($state)) {
            if ($state) {
                $this->addClass($className);
            } else {
                $this->removeClass($className);
            }
            return;
        }

        if (!$this->hasAttr('class')) {
            $this->setAttr('class', $className);
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
            $this->removeClass($className);
            return;
        }

        $this->setAttr('class', implode(' ', $classArr));
    }

    /**
     * Get the value of a computed style property
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getCss(string $name)
    {
        $style = (string) $this->getAttr('style');
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
    }

    /**
     * Set or Remove one CSS property.
     *
     * @param string      $name
     * @param string|null $value
     */
    public function setCss(string $name, ?string $value)
    {
        if ((string) $value === '') {
            $this->removeCss($name);
            return;
        }

        $style = (string) $this->getAttr('style');
        if (!$style) {
            $this->setAttr('style', $name . ': ' . $value . ';');
            return;
        }

        $css = Helper::splitCss($style);
        if (!array_key_exists($name, $css)) {
            $keys = Helper::caseInsensitiveSearch($name, array_keys($css));
            foreach ($keys as $key) {
                unset($css[$key]);
            }
        }

        $css[$name] = $value;
        $style = Helper::implodeCss($css);
        $this->setAttr('style', $style);
    }

    /**
     * Remove one CSS property.
     *
     * @param string $name
     */
    public function removeCss(string $name)
    {
        $style = (string) $this->getAttr('style');

        if ($style !== '') {
            $css = Helper::splitCss($style);
            $keys = Helper::caseInsensitiveSearch($name, array_keys($css));

            if (!empty($keys)) {
                foreach ($keys as $key) {
                    unset($css[$key]);
                }

                $style = Helper::implodeCss($css);
                $this->setAttr('style', $style);
            }
        }
    }
}
