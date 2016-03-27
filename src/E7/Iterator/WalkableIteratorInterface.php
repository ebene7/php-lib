<?php

namespace E7\Iterator;

interface WalkableIteratorInterface extends \Iterator
{
    /**
     * @param   string $method
     * @return  array
     */
    public function each($method);
        
    /**
     * @param   callable $callback
     * @return  array
     */
    public function walk(callable $callback);
}

