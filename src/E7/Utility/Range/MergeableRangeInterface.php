<?php

namespace E7\Utility\Range;

use E7\Utility\RangeInterface;

interface MergeableInterface extends RangeInterface
{
    const MERGE_TOUCH = 'touch';
    const MERGE_OVERLAP = 'overlap';

    /**
     * @param   \E7\Utility\RangeInterface $ranges
     * @return  \E7\Utility\RangeInterface Description
     */
    public function merge(RangeInterface ...$ranges);
}

