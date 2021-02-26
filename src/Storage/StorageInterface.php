<?php

namespace Lemo\Grid\Storage;

use ArrayIterator;

interface StorageInterface
{
    /**
     * Returns true if and only if storage is empty
     *
     * @param  string $key
     * @return bool
     */
    public function isEmpty($key);

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @param  string $key
     * @return ArrayIterator
     */
    public function read($key);

    /**
     * Writes $contents to storage
     *
     * @param  string        $key
     * @param  ArrayIterator $content
     * @return void
     */
    public function write($key, $content);

    /**
     * Clears contents from storage
     *
     * @param  string $key
     * @return void
     */
    public function clear($key);
}
