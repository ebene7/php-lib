<?php

namespace E7\Utility\Range;

use E7\Utility\RangeInterface;

/**
 * Interface for mergeable ranges
 */
interface MergeableRangeInterface extends RangeInterface
{
    const MERGE_TOUCH = 'touch';
    const MERGE_OVERLAP = 'overlap';
    
    /**
     * Merges this object with another
     * 
     * @param  \E7\Utility\RangeInterface $range
     * @param  string $type
     * @return \E7\Utility\RangeInterface
     */
    public function merge(RangeInterface $range, $type = self::MERGE_OVERLAP);
}
