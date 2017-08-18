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

        foreach($this as $key => $value) {
            $results[] = call_user_func_array($callback, [$key, $value]);
        }

        return $results;
    }
}