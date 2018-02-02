<?php

namespace E7\Date;

use E7\Utility\RangeInterface;
use E7\Utility\AbstractRange;

/**
 * DateRange
 */
class DateRange extends AbstractRange implements DateRangeInterface
{
    /** @var \DateTimeInterface */
    private $from;

    /** @var \DateTimeInterface */
    private $lowerFrom;

    /** @var \DateTimeInterface */
    private $to;

    /** @var \DateTimeInterface */
    private $higherTo;

    /**
     * Constructor
     * 
     * @param \DateTimeInterface|string $from
     * @param \DateTimeInterface|string $to
     * @param array $options
     */
    function __construct($from, $to, array $options = [])
    {
        $from = $this->prepareDate($from, RangeInterface::TYPE_FROM);
        $to = $this->prepareDate($to, RangeInterface::TYPE_TO);
        
        // prepare default options
        $options = array_merge(
            $options,
            []
        );
        $this->setOptions($options);

        // make sure "from" is always lower than "to"
        $this->from = min($from, $to);
        $this->to = max($from, $to);

        $lowerFrom = clone $this->from;
        $this->lowerFrom = $lowerFrom->modify('-1 day');

        $higherTo = clone $this->to;
        $this->higherTo = $higherTo->modify('+1 day');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '[' .  $this->getFrom()->format('Y-m-d') . '-' . $this->getTo()->format('Y-m-d') . ']';
    }
    
    /**
     * @return \DateTimeInterface
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLowerFrom()
    {
        return $this->lowerFrom;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getHigherTo()
    {
        return $this->higherTo;
    }

    /**
     * Get DateInterval
     *
     * @return \DateInterval
     */
    public function getInterval()
    {
        return $this->getTo()->diff($this->getFrom());
    }

    /**
     * Get DatePeriod
     *
     * @return \DatePeriod
     */
    public function getPeriod()
    {
        return new \DatePeriod($this->getFrom(), new \DateInterval('P1D'), $this->getTo());
    }
    
    /**
     * {@inheritDoc}
     */
    public function contains($value)
    {
        $value = $this->prepareDate($value);
        if (!$value instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('Value must be an instance of \DateTimeInterface');
        }
        return parent::contains($value);
    }

    /**
     * {@inheritDoc}
     */
    public function checkCollision(RangeInterface $range)
    {
        if (!$range instanceof DateRangeInterface) {
            throw new \InvalidArgumentException('Range must be an instance of \E7\Date\DateRangeInterface'); 
        }
        return parent::checkCollision($range);
    }
        
    /**
     * {@inheritDoc}
     */
    public function checkTouch(RangeInterface $range)
    {
        if (!$range instanceof DateRangeInterface) {
            throw new \InvalidArgumentException('Range must be an instance of \E7\Date\DateRangeInterface'); 
        }
        return parent::checkTouch($range);
    }
    
    /**
     * @param   \DateTimeInterface|string $date
     * @param   string $type
     * @return  \DateTimeInterface
     */
    protected function prepareDate($date, $type = null)
    {
        // work internal with cloned object, because they will be modified
        $date = $date instanceof \DateTimeInterface ? clone $date : new \DateTime($date);
        
        switch($type) {
            case RangeInterface::TYPE_FROM:
                $date->setTime(0, 0, 0);
                break;
            case RangeInterface::TYPE_TO:
                $date->setTime(23, 59, 59);
                break;
            default:
                /* do nothing, relax */
                break;
        }
        
        return $date;
    }
}
