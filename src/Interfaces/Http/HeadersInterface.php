<?php namespace Heimdall\Interfaces\Http;

use Heimdall\interfaces\CollectionInterface;

/**
 * Part of Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 *
 * Interface HeadersInterface
 * @package Heimdall\Interfaces\Http
 */
interface HeadersInterface extends CollectionInterface
{
    /**
     * Add HTTP header value
     *
     * This method appends a header value. Unlike the set() method,
     * this method _appends_ this new value to any values
     * that already exist for this header name.
     *
     * @param string $key The case-insensitive header name
     * @param string|string[] $value The new header value(s)
     */
    public function add($key, $value);

    /**
     * Normalize header name
     *
     * This method transforms header names into a
     * normalized form. This is how we enable case-insensitive
     * header names in the other methods in this class.
     *
     * @param string $key The case-insensitive header name
     *
     * @return string Normalized header name
     */
    public function normalizeKey($key);
}
