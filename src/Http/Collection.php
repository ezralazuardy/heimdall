<?php namespace Heimdall\Http;

use ArrayIterator;
use Heimdall\Interfaces\CollectionInterface;

/**
 * Part of Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 *
 * Collection
 *
 * This class provides a common interface used by many other
 * classes in a Slim application that manage "collections"
 * of data that must be inspected and/or manipulated
 *
 * @package Heimdall\Src
 */
class Collection implements CollectionInterface
{
    /**
     * The source data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @param array $items Pre-populate collection with this key-value array
     */
    public function __construct(array $items = [])
    {
        $this->replace($items);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Get collection keys
     *
     * @return array The collection's source data keys
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get collection item for key
     *
     * @param string $key The data key
     *
     * @return mixed The key's value, or the default value
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * Set collection item
     *
     * @param string $key The data key
     * @param mixed $value The data value
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get number of items in collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get collection iterator
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }
}
