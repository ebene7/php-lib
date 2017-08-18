<?php

namespace E7\Iterator;

trait ToArrayTrait
{
    /**
     * Copy the iterator into an array
     *
     * @param   boolean $useKeys
     * @return  array
     */
    public function toArray($useKeys = true)
    {
        return iterator_to_array($this, $useKeys);
    }
}