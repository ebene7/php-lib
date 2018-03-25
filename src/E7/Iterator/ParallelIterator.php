<?php

namespace E7\Iterator;

/**
 * ParallelIterator - Iterate over multiple arrays/iterators at the same time
 */
class ParallelIterator implements \Iterator, \Countable
{
    /** @var iterable */
    private $data;
    
    /** @var integer */
    private $position;
    
    /** @var integer */
    private $count = 0;
    
    /**
     * Constructor
     * 
     * @param  iterable
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $args = func_get_args();
        $data = 1 == func_num_args() ? $args[0] : $args;
        
        if (!is_iterable($data)) {
            throw new \InvalidArgumentException();
        }
        
        foreach ($data as $item) {
            if (!is_iterable($item)) {
                throw new \InvalidArgumentException();
            }
            $this->count = max($this->count, count($item));
        }
        
        $this->data = $data;
    }
    
    /**
     * {@inheritDoc}
     */
    public function current()
    {
        $current = [];
        
        foreach ($this->data as $key => $value) {
            $current[$key] = current($value);
        }
        
        return $current;
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        foreach ($this->data as $key => &$value) {
            false !== next($value);
        }
        $this->position++;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->position = 0;
        reset ($this->data);
        
        foreach ($this->data as $key => &$value) {
            reset($value);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return $this->position < $this->count;
    }

    /**
     * {@inheritDoc}
     */
    public function count() 
    {
        return $this->count;
    }
}
