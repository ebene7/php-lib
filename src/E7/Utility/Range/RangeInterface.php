<?php

namespace E7\Utility\Range;

/**
 * Interface for ranges
 */
interface RangeInterface
{
    const TYPE_FROM = 'from';
    const TYPE_TO   = 'to';
    
    /**
     * Return the start value to compare
     * 
     * @return  mixed
     */
    public function getFrom();
    
    /**
     * Return the end value to compare
     * 
     * @return  mixed
     */
    public function getTo();
    
    /**
     * Return a lower from value to compare
     * 
     * @return  mixed
     */
    public function getLowerFrom();
    
    /**
     * Return a higher to value to compare
     * 
     * @return  mixed
     */
    public function getHigherTo();
    
    /**
     * Check if the value is between from and to
     * 
     * @param  mixed $value
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function contains($value);
            
    /**
     * Check if the ranges overlap eachother
     * 
     * @param   \E7\Utility\RangeInterface $range
     * @return  boolean
     * @throws  \InvalidArgumentException
     */
    public function checkCollision(RangeInterface $range);
    
    /**
     * Check if the ranges touch and could combined
     * 
     * @param   \E7\Utility\RangeInterface $range
     * @return  boolean
     * @throws  \InvalidArgumentException
     */
    public function checkTouch(RangeInterface $range);
}