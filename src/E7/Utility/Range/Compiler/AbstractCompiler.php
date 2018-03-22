<?php

namespace E7\Utility\Range\Compiler;

use E7\Utility\Range\MergeableRangeInterface;

/**
 * Baseclass for RangeCompiler
 */
abstract class AbstractCompiler implements CompilerInterface
{
    /** @var string */
    private $mergeType = MergeableRangeInterface::MERGE_OVERLAP;

    /**
     * Set merge type
     *
     * @param  string $mergeType
     * @return AbstractCompiler
     */
    public function setMergeType($mergeType)
    {
        $this->mergeType = $mergeType;
        return $this;
    }

    /**
     * Get merge type
     *
     * @return string
     */
    public function getMergeType() {
        return $this->mergeType;
    }
}
