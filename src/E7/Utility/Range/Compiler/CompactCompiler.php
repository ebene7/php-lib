<?php

namespace E7\Utility\Range\Compiler;

use E7\Utility\Range\MergeableRangeInterface;

class CompactCompiler extends AbstractCompiler
{
    /** @var integer */
    private $maxIterations = 0;

    /**
     * {@inheritDoc}
     */
    public function compile($ranges)
    {
        $loops = 0;

        do {
            $tmpRanges = [];
            $action = false;

            foreach ($ranges as $range) {
                $added = false;

                foreach ($tmpRanges as &$tmpRange) {
                    if (!$tmpRange->equals($range) && $tmpRange->checkTouch($range)) {
                        $tmpRange = $tmpRange->merge($range, MergeableRangeInterface::MERGE_TOUCH);
                        $added = true;
                        $action = true;
                    }
                }

                if (!$added) {
                    $tmpRanges[] = $range;
                }
            }

            $ranges = $tmpRanges;
            $loops++;
        } while ($action && !($this->maxIterations != 0 && $loops >= $this->maxIterations));

        return $tmpRanges;
    }
}

