<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function assertHtmlEquals($expectedHtml, $actualHtml)
    {
        $this->assertEquals(
            $this->washHtml($expectedHtml),
            $this->washHtml($actualHtml)
        );
    }

    protected function washHtml(string $html)
    {
        $html = trim($html);
        //<!DOCTYPE html>
        $tagRegex = '<(?:/|!)?[a-z\d]+(?:\s[^<>]+)?>';
        $arr = preg_split(
            '~(' . $tagRegex . ')~iu',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        $arr = array_filter($arr, function ($value) {
            return trim($value) !== '';
        });

        return implode('', $arr);
    }

    /**
     * Return a closure to test protected method
     *
     * @param object $instance
     * @param string $method
     *
     * @return Closure
     */
    protected function protectMethod($instance, $method)
    {
        $class = new ReflectionClass(get_class($instance));
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return function () use ($instance, $method) {
            return $method->invokeArgs($instance, func_get_args());
        };
    }
}
