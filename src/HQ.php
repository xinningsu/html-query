<?php

namespace Sulao\HtmlQuery;

use DOMDocument;

/**
 * Class HQ
 *
 * @package Sulao\HtmlQuery
 */
class HQ
{
    /**
     * Generate a new instance with the html
     *
     * @param string $html
     *
     * @return HtmlDocument
     */
    public static function html(string $html)
    {
        return static::instance($html);
    }

    /**
     * Generate a new instance with the html file
     *
     * @param string $file
     *
     * @return HtmlDocument
     */
    public static function htmlFile(string $file)
    {
        return static::html(file_get_contents($file));
    }

    /**
     * Generate a new instance, optionally with the html
     *
     * @param string|null $html
     *
     * @return HtmlDocument
     */
    public static function instance(?string $html = null)
    {
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        if (!is_null($html)) {
            $doc->loadHTML($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        }

        return new HtmlDocument($doc);
    }
}
