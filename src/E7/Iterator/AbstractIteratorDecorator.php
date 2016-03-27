<?php

namespace E7\Iterator;

abstract class AbstractIteratorDecorator implements \Iterator
{
    /**
     * @var \Iterator
     */
    private $inner;
    
    /**
     * Constructor
     * 
     * @param   \Iterator $inner
     */
    public function __construct(\Iterator $inner)
    {
        $this->inner = $inner;
    }
    
    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->inner->current();
    }
    
    /**
     * {@inheritDoc}
     */
    public function key() 
    {
        return $this->inner->key();
    }
    
    /**
     * {@inheritDoc}
     */
    public function next() 
    {
        $this->inner->next();
    }
    
    /**
     * {@inheritDoc}
     */
    public function rewind() 
    {
        $this->inner->rewind();
    }
    
    /**
     * {@inheritDoc}
     */
    public function valid() 
    {
        return $this->inner->valid();
    }
}