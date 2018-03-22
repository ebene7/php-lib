<?php

namespace E7\Utility\Range;

use E7\Iterator\WalkableIterator;

/**
 * Collection for Ranges
 */
class Collection extends WalkableIterator
{
    /** @var mixed */
    private $min;

    /** @var mixed */
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

    /**
     * @throws \InvalidArgumentException
     */
    protected function init()
    {
        foreach ($this as $range) {
            if (!$this->accept($range)) {
                throw new \InvalidArgumentException();
            }

            $this->min = null === $this->min
                ? $range->getFrom()
                : min($this->min, $range->getFrom());
            $this->max = null === $this->max
                ? $range->getTo()
                : max($this->max, $range->getTo());
        }
    }

    /**
     * Get min value
     *
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Get max value
     *
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param  RangeInterface $range
     * @return RangeCollection
     * @throws \InvalidArgumentException
     */
    public function add($range)
    {
        if (!$this->accept($range)) {
            throw new \InvalidArgumentException();
        }

        $this->getInnerIterator()->append($range);
        $this->min = min($this->min, $range->getFrom());
        $this->max = max($this->max, $range->getTo());

        return $this;
    }

    /**
     * @param  mixed $item
     * @return boolean
     */
    public function accept($item)
    {
        return $item instanceof RangeInterface;
    }
}
