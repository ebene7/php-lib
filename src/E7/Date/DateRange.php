<?php

namespace E7\Date;

use E7\Utility\RangeInterface;
use E7\Utility\AbstractRange;

class DateRange extends AbstractRange implements DateRangeInterface
{
    /**
     * @var \DateTimeInterface
     */
    private $from;

    /**
     * @var \DateTimeInterface
     */
    private $to;

    /**
     * Constructor
     *
     * @param   \DateTimeInterface|string $from
     * @param   \DateTimeInterface|string $to
     */
    function __construct($from, $to)
    {
        $from = $this->prepareDate($from, RangeInterface::TYPE_FROM);
        $to = $this->prepareDate($to, RangeInterface::TYPE_TO);

        $this->from = min($from, $to);
        $this->to = max($from, $to);
    }

    public function __toString()
    {
        return '[' .  $this->getFrom()->format('Y-m-d') . '-' . $this->getTo()->format('Y-m-d') . ']';
    }

    /**
     * @return  \DateTimeInterface
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return  \DateTimeInterface
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * {@inheritDoc}
     */
    public function modifyValue($value, $modifier)
    {
        $value = $this->prepareDate($value);
        if (!$value instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('Parameter 1 must be an instance of \DateTimeInterface');
        }

        $value = clone $value;

        switch($modifier) {
            case self::MODIFIER_LOWER:
                return $value->modify('-1 day');
            case self::MODIFIER_HIGHER:
                return $value->modify('+1 day');
            default:
                throw new \InvalidArgumentException("Unknown modifier '$modifier'");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function valueToString($value)
    {
        // @TODO
        // - implement precision
        // - outsource string conversion
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function contains($value)
    {
        $value = $this->prepareDate($value);
        if (!$value instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('Parameter 1 must be an instance of \DateTimeInterface');
        }
        return parent::contains($value);
    }

    /**
     * {@inheritDoc}
     */
    public function compareTo($object)
    {
        $comperator = new DateRangeComparator();
        return $comperator->compare($this, $object);
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