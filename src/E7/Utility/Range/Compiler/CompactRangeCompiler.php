<?php

namespace E7\Utility\Range\Compiler;

class CompactCompiler extends AbstractCompiler
{
    /** @var integer */
    private $maxIterations = 0;

    /**
     * {@inheritDoc}
     */
    public function compile($ranges)
    {
        $running = true;
        $max = 0;
        $loop = 0;

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

            if (!$action || ($max != 0 && $loop >= $max)) {
                $running = false;
            }

            $ranges = $tmpRanges;
            $loop++;
        } while ($running);

        return $tmpRanges;
    }
}

