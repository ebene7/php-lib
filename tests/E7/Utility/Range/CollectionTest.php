<?php

namespace E7\Utility\Range;

use PHPUnit\Framework\TestCase;
use E7\Utility\Range\Collection;

class CollectionTest extends TestCase
{
    public function testConstructorAndInit()
    {
        $ranges = [
            new NumberRange(5, 10),
            new NumberRange(3, 13),
            new NumberRange(1, 30),
        ];

        $collection = new Collection($ranges);

        $this->assertCount(count($ranges), $collection);
        $this->assertEquals(1, $collection->getMin());
        $this->assertEquals(30, $collection->getMax());
    }

    /**
     * @dataProvider      providerAddWithWrongType
     * @expectedException \InvalidArgumentException
     * @param             mixed $item
     */
    public function testAddWithWrongType($item)
    {
        $collection = new Collection();
        $collection->add($item);
    }

    /**
     * Dataprovider
     *
     * @return array
     */
    public function providerAddWithWrongType()
    {
        return [
            [ new \stdClass() ],
            [ 'this-is-invalid' ],
        ];
    }
}

