<?php namespace Heimdall\Http;

use Psr\Http\Message\StreamInterface;

/**
 * Part of Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 *
 * Represents a non-readable stream that whenever it is written pushes
 * the data back to the browser immediately.
 *
 * @package Heimdall\Http
 */
class NonBufferedBody implements StreamInterface
{
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        $buffered = '';
        while (0 < ob_get_level()) {
            $buffered = ob_get_clean() . $buffered;
        }
        echo $buffered . $string;
        flush();
        return strlen($string) + strlen($buffered);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null): ?array
    {
        return null;
    }
}
