<?php

namespace E7\Iterator;

trait WalkableIteratorWalkTrait
{
    /**
     * @param   callable $callback
     * @return  array
     */
    public function walk(callable $callback)
    {
        $results = [];
        
        foreach($this as $item) {
            $results[] = call_user_func($callback, $item);
        }
        
        return $results;
    }
}