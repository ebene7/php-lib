<?php

namespace E7\Iterator;

trait WalkableIteratorEachTrait
{
    /**
     * @param   string $method
     * @return  array
     */
    public function each($method)
    {
        $args = func_get_args();
        $method = array_shift($args);
        
        $results = [];
        
        foreach($this as $item) {
            $results[] = call_user_func_array([$item, $method], $args);
        }
        
        return $results;
    }
}

