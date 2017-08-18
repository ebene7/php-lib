<?php

namespace E7\Utility\Range\Compiler;

/**
 * An interface for all range compilers
 */
interface CompilerInterface
{
    public function compile($ranges);
}