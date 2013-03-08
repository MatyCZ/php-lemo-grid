<?php

namespace LemoGrid;

interface ColumnAttributeRemovalInterface
{
    /**
     * Remove a single element attribute
     *
     * @param  string $key
     * @return ColumnAttributeRemovalInterface
     */
    public function removeAttribute($key);

    /**
     * Remove many attributes at once
     *
     * @param array $keys
     * @return ColumnAttributeRemovalInterface
     */
    public function removeAttributes(array $keys);

    /**
     * Remove all attributes at once
     *
     * @return ColumnAttributeRemovalInterface
     */
    public function clearAttributes();
}
