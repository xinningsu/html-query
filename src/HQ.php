<?php

namespace Sulao\HtmlQuery;

use Dom\HTMLDocument as DOMDocument;

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

        if ($html !== null) {
            $doc = DOMDocument::createFromString(
                $html,
                LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED //| \Dom\HTML_NO_DEFAULT_NS
            );
        } else {
            $doc = DOMDocument::createEmpty();
        }

        return new HtmlDocument($doc);
    }
}
