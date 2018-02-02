<?php

namespace E7\Iterator;

use PHPUnit\Framework\TestCase;

class WalkableIteratorTest extends TestCase
{
    public function testImplementsIteratorInterface()
    {
        $iterator = new WalkableIterator(new \ArrayIterator([]));
        $this->assertInstanceOf(\Iterator::class, $iterator);
        $this->assertTrue(method_exists($iterator, 'current'));
        $this->assertTrue(method_exists($iterator, 'key'));
        $this->assertTrue(method_exists($iterator, 'next'));
        $this->assertTrue(method_exists($iterator, 'valid'));
        $this->assertTrue(method_exists($iterator, 'rewind'));
    }
    
    public function testImplementsWalkableIteratorInterface()
    {
        $iterator = new WalkableIterator(new \ArrayIterator([]));
        $this->assertInstanceOf(WalkableIterator::class, $iterator);
        $this->assertTrue(method_exists($iterator, 'each'));
        $this->assertTrue(method_exists($iterator, 'walk'));
    }
}
