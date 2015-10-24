<?php

namespace app\Services\Transformer;

abstract class Transformer implements TransformerInterface
{
    /**
     * Transforms a collection.
     *
     * @param $collection
     * @return array
     */
    public function transformCollection($collection)
    {
        return array_map(array($this, 'transform'), $collection);
    }
}
