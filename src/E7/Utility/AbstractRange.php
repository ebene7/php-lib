<?php

namespace E7\Utility;

use E7\Utility\Range\MergeableRangeInterface;

/**
 * Baseclass for ranges
 */
abstract class AbstractRange implements MergeableRangeInterface
{
    /** @var array */
    private $options = [];

    /**
     * Set options
     * 
     * @param  array $options
     * @return AbstractRange
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }
    
    /**
     * Get options
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function contains($value)
    {
        /* if $value is a range, check if both value (from/to) contained */
        if ($value instanceof RangeInterface) {
            return $this->contains($value->getFrom())
                   && $this->contains($this->getTo());
        }
        
        /* check single value */
        return $this->getFrom() <= $value && $this->getTo() >= $value;
    }
    
    /**
     * 
     * @param   object $object
     * @return  boolean
     */
    public function equals($object)
    {
        return is_object($object)
               && $object instanceof AbstractRange
               && $this->getFrom() == $object->getFrom()
               && $this->getTo() == $object->getTo();
    }
    
    /**
     * {@inheritDoc}
     */
    public function checkCollision(RangeInterface $range)
    {
        return $this->contains($range->getFrom()) 
               || $this->contains($range->getTo())
               || $range->contains($this->getFrom())
               || $range->contains($this->getTo());
    }
        
    /**
     * {@inheritDoc}
     */
    public function checkTouch(RangeInterface $range)
    {
        return $this->contains($range->getLowerFrom())
               || $this->contains($this->getHigherTo())
               || $range->contains($this->getLowerFrom())
               || $range->contains($this->getHigherTo());
    }
    
    /**
     * Find the intersection
     * 
     * @param   \E7\Utility\RangeInterface $range
     * @return  \E7\Utility\RangeInterface|null
     */
    public function getIntersection(RangeInterface $range)
    {
        $from = null;
        $to = null;
        
        // find the from value
        if ($this->contains($range->getFrom())) {
            $from = $range->getFrom();
        } else if ($range->contains($this->getFrom())) {
            $from = $this->getFrom();
        }
        
        // find the to value
        if ($this->contains($range->getTo())) {
            $to = $range->getTo();
        } else if ($range->contains($this->getTo())) {
            $to = $this->getTo();
        }
        
        // check values
        if (empty($from)
            || empty($to)) {
            return null;
        }

        // return intersection range
        return self::create($from, $to, $this->getOptions());
    }
    
    public function getDifference(RangeInterface $range)
    {   
        if ($this->equals($range)) { /* $range is completely included */
            return [];
        }
        
        $ranges = [clone $this, clone $range]; /* clone, because this array could be returned */
        
        if (!$this->checkTouch($range)) { /* both ranges are different */
            return $ranges;
        }
        
        if ($this->getFrom() == $range->getFrom()) { /* same from, difference in the end */
            if ($this->getTo() < $range->getTo()) {
                $from = $this->getHigherTo();
                $to = $range->getTo();
            } else {
                $from = $range->getHigherTo();
                $to = $this->getTo();
            }
            return [self::create($from, $to, $this->getOptions())];
        }
        
        if ($this->getTo() == $range->getTo()) { /* same to, difference in the beginning */
            if ($this->getFrom() < $range->getFrom()) {
                $from = $this->getFrom();
                $to = $range->getLowerFrom();
            } else {
                $from = $range->getFrom();
                $to = $this->getLowerFrom();
            }
            return [self::create($from, $to, $this->getOptions())];
        }
        
        
//        if ($this->getFrom() != $range->getFrom()
//            && $this->getTo() != $range->getto()) {
//            if ($)
//        }
        
    }
    
    /**
     * {inheritDoc}
     */
    public function merge(RangeInterface $range, $type = self::MERGE_TOUCH)
    {
        if ((self::MERGE_OVERLAP == $type && !$this->checkCollision($range))
            || (self::MERGE_TOUCH == $type && !$this->checkTouch($range))) {
            /* impossible to merge */
            return null;
        }

        $mergedRange = self::create(
            min($this->getFrom(), $range->getFrom()),
            max($this->getTo(), $range->getTo()),
            $this->getOptions()
        );
        
        if (method_exists($mergedRange, 'afterMerge')) {
            call_user_func([$mergedRange, 'afterMerge'], $this, $range);
        }
        
        return $mergedRange;
    }
    
    /**
     * Factorymethod
     * 
     * @param  mixed $from
     * @param  mixed $to
     * @param  array $options
     * @return \E7\Utility\RangeInterface 
     */
    public static function create()
    {
        $reflection = new \ReflectionClass(\get_called_class());
        return $reflection->newInstanceArgs(func_get_args());
    }
}
