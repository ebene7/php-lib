<?php

namespace E7\Utility\Range;

/**
 * Baseclass for ranges
 */
abstract class AbstractRange implements MergeableRangeInterface
{
    /** @var mixed */
    private $from;

    /** @var mixed */
    private $to;

    /** @var mixed */
    private $lowerFrom;

    /** @var mixed */
    private $higherTo;

    /** @var array */
    private $options = [];

    /**
     * @param mixed $from
     * @param mixed $to
     * @param array $options
     */
    public function __construct($from, $to = null, array $options = [])
    {
        $this->setOptions($options);

        $from = $this->prepareValue($from, RangeInterface::TYPE_FROM);
        $to = null !== $to
            ? $this->prepareValue($to, RangeInterface::TYPE_TO)
            : $from;

        // make sure "from" is always lower than "to"
        $this->from = min($from, $to);
        $this->to = max ($from, $to);

        $this->lowerFrom = $this->prepareValue($this->from, RangeInterface::TYPE_LOWER_FROM);
        $this->higherTo = $this->prepareValue($this->to, RangeInterface::TYPE_HIGHER_TO);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '[' . $this->getFrom() . '-' . $this->getTo() . ']';
    }

    /**
     * Get from value
     *
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Get to value
     *
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Get lower from value
     *
     * @return mixed
     */
    public function getLowerFrom()
    {
        return $this->lowerFrom;
    }

    /**
     * Get higher to value
     *
     * @return mixed
     */
    public function getHigherTo()
    {
        return $this->higherTo;
    }

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
               || $this->contains($range->getHigherTo())
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

        return [];
    }

    /**
     * {inheritDoc}
     */
    public function merge(RangeInterface $range, $type = self::MERGE_OVERLAP)
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
     * Prepare $value depending on given $type
     *
     * @param  mixed $value
     * @param  string $type
     * @return mixed
     */
    protected abstract function prepareValue($value, $type = null);

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
