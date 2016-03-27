<?php

namespace E7\Iterator;

class PermutationIterator implements \Iterator
{
    /**
     * @var array
     */
    private $inner = [];

    /**
     * Constructor
     *
     * @param  ... Variable list of traversable elements
     * @throws  \InvalidArgumentException
     */
    public function __construct()
    {
        foreach (func_get_args() as $inner) {
            if ($inner instanceof \IteratorAggregate) {
                $inner = $inner->getIterator();
            } else
                if (is_array($inner)) {
                $inner = new \ArrayIterator($inner);
            }

            if (!$inner instanceof \Iterator) {
                throw new \InvalidArgumentException('All parameters are expected an instance of Iterator');
            }

            $this->inner[] = $inner;
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function current()
    {
        $current = [];
        foreach ($this->inner as $inner) {
            $current[] = $inner->current();
        }
        return $current;
    }
    
    /**
     * {@inheritDoc}
     */
    public function key()
    {
        $keys = [];
        foreach ($this->inner as $inner) {
            $keys[] = $inner->key();
        }
        return implode('-', $keys);
    }
    
    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $count = count($this->inner);

        for ($i = $count-1; $i >= 0; $i--) {
            $this->inner[$i]->next();

            if ($this->inner[$i]->valid()) {
                return;
            }

            if ($i > 0) {
                $this->inner[$i]->rewind();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        foreach ($this->inner as $inner) {
            $inner->rewind();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        foreach ($this->inner as $inner) {
            if (!$inner->valid()) {
                return false;
            }
        }

        return true;
    }
}