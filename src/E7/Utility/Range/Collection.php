<?php

namespace E7\Utility\Range;

use E7\Iterator\WalkableIterator;

class Collection extends WalkableIterator
{
    /**
     * @var mixed
     */
    private $min;

    /**
     * @var mixed
     */
    private $max;

    /**
     * Constructor
     *
     * @param   array|\ArrayIterator $ranges
     */
    public function __construct($ranges)
    {
        if (is_array($ranges)) {
            $ranges = new \ArrayIterator($ranges);
        }

        parent::__construct($ranges);
        $this->init();
    }

    protected function init()
    {
        foreach ($this as $range) {
            $this->min = null === $this->min
                ? $range->getFrom()
                : min($this->min, $range->getFrom());

            $this->max = null === $this->max
                ? $range->getTo()
                : max($this->max, $range->getTo());
        }
    }

}

