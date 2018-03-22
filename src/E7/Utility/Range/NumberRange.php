<?php

namespace E7\Utility\Range;

class NumberRange extends AbstractRange
{
    private $step = 1;

    /**
     * {@inheritDoc}
     */
    protected function prepareValue($value, $type = null)
    {
        $value = intval($value);

        switch($type) {
            case RangeInterface::TYPE_LOWER_FROM:
                $value -= $this->step;
                break;
            case RangeInterface::TYPE_HIGHER_TO:
                $value += $this->step;
                break;
            default:
                /* do nothing, relax */
                break;
        }

        return $value;
    }
}