<?php

namespace E7\Date;

use E7\Utility\Range\RangeInterface;
use E7\Utility\Range\AbstractRange;

/**
 * DateRange
 *
 * @method \DateTimeInterface getFrom()
 * @method \DateTimeInterface getTo()
 * @method \DateTimeInterface getLowerFrom()
 * @method \DateTimeInterface getHigherTo()
 */
class DateRange extends AbstractRange implements DateRangeInterface
{
    /**
     * @return string
     */
    public function __toString()
    {
        return '[' .  $this->getFrom()->format('Y-m-d') . '-' . $this->getTo()->format('Y-m-d') . ']';
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
        $value = $this->prepareValue($value);
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
     * {@inheritDoc}
     */
    protected function prepareValue($value, $type = null)
    {
        // work internal with cloned object, because they will be modified
        $value = $value instanceof \DateTimeInterface ? clone $value : new \DateTime($value);

        switch($type) {
            case RangeInterface::TYPE_FROM:
                $value->setTime(0, 0, 0);
                break;
            case RangeInterface::TYPE_TO:
                $value->setTime(23, 59, 59);
                break;
            case RangeInterface::TYPE_LOWER_FROM:
                $value->modify('-1 day');
                break;
            case RangeInterface::TYPE_HIGHER_TO:
                $value->modify('+1 day');
                break;
            default:
                /* do nothing, relax */
                break;
        }

        return $value;
    }
}
