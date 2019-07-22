<?php

namespace Sulao\HtmlQuery;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Traversable;

/**
 * Class Helper
 *
 * @package Sulao\HtmlQuery
 */
class Helper
{
    /**
     * Convert a css selector to xpath
     *
     * @param string $selector
     * @param string $prefix
     *
     * @return string
     */
    public static function toXpath(
        string $selector,
        string $prefix = 'descendant::'
    ): string {
        static $converter;
        $converter = $converter ?: new CssSelectorConverter();

        return $converter->toXPath($selector, $prefix);
    }

    /**
     * Strict Array Unique
     *
     * @param array|Traversable $arr
     *
     * @return array
     */
    public static function strictArrayUnique($arr): array
    {
        $uniqueArr = [];
        foreach ($arr as $value) {
            if (!in_array($value, $uniqueArr, true)) {
                $uniqueArr[] = $value;
            }
        }

        return $uniqueArr;
    }

    /**
     * Strict array intersect
     *
     * @param array $arr1
     * @param array $arr2
     *
     * @return array
     */
    public static function strictArrayIntersect(array $arr1, array $arr2): array
    {
        $arr = array_filter($arr1, function ($val1) use ($arr2) {
            return in_array($val1, $arr2, true);
        });

        return array_values($arr);
    }

    /**
     * Strict array diff
     *
     * @param array $arr1
     * @param array $arr2
     *
     * @return array
     */
    public static function strictArrayDiff(array $arr1, array $arr2): array
    {
        $arr = array_filter($arr1, function ($val1) use ($arr2) {
            return !in_array($val1, $arr2, true);
        });

        return array_values($arr);
    }

    /**
     * Split the class attr value to a class array
     *
     * @param string $className
     *
     * @return array
     */
    public static function splitClass(string $className): array
    {
        return preg_split('/\s+/', trim($className)) ?: [];
    }

    /**
     * Split style to css array
     *
     * @param string $style
     *
     * @return array
     */
    public static function splitCss(string $style): array
    {
        $arr = explode(';', $style);
        $arr = array_map('trim', $arr);

        $css = [];
        foreach ($arr as $value) {
            $row = explode(':', $value, 2);
            if (count($row) !== 2) {
                continue;
            }

            $css[trim($row[0])] = trim($row[1]);
        }

        return $css;
    }

    /**
     * Implode css array to style string
     *
     * @param array $css
     *
     * @return string
     */
    public static function implodeCss(array $css): string
    {
        $arr = [];
        foreach ($css as $key => $value) {
            $arr[] = $key . ': ' . $value;
        }

        $style = $arr ? implode('; ', $arr) . ';' : '';

        return $style;
    }

    /**
     * Determine whether the string is raw html,
     * otherwise consider it as a css selector
     *
     * @param string $html
     *
     * @return bool
     */
    public static function isRawHtml(string $html): bool
    {
        if ($html[0] === '<' && $html[-1] === '>' && strlen($html) >= 3) {
            return true;
        }

        return (bool) preg_match('/^\s*(<[\w\W]+>)[^>]*$/', $html);
    }

    /**
     * Determine whether the selector is a id selector
     *
     * @param string      $selector
     * @param string|null $id
     *
     * @return bool
     */
    public static function isIdSelector(
        string $selector,
        string &$id = null
    ): bool {
        if (preg_match('/^#([\w-]+)$/', $selector, $match)) {
            $id = $match[1];

            return true;
        }

        return false;
    }
}
