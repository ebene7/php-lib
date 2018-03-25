<?php

namespace E7\Iterator;

use PHPUnit\Framework\TestCase;

class ParallelIteratorTest extends TestCase
{
    /**
     * @dataProvider      providerConstructorWithException
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithException(array $parameters)
    {
        new ParallelIterator($parameters);
    }
    
    /**
     * @return array
     */
    public function providerConstructorWithException()
    {
        return [
            [  /* root data is not an array */
                ['not-an-array']
            ],
        ];
    }
    
    /**
     * @dataProvider providerIterator
     * @param        array $parameters
     * @param        array $expected
     */
    public function testIterator(array $parameters, array $expected)
    {
        $reflection = new \ReflectionClass(ParallelIterator::class);
        $iterator = $reflection->newInstanceArgs($parameters['arguments']);
        
        $this->assertCount($expected['count'], $iterator);
        
        foreach ($iterator as $index => $item) {
            $this->assertInternalType('array', $item);
            $this->assertCount($expected['sub_count'], $item);
            $this->assertEquals($expected['sub_keys'], array_keys($item));
            $this->assertEquals($expected['items'][$index], $item);
        }
        
        $this->assertTrue(true);
    }
    
    /**
     * @return array
     */
    public function providerIterator()
    {
        $data = [
            0 => [ '[a,1,a1]', '[b,2,b2]', '[c,3,c3]' ],
            'letter' => [ 'a', 'b', 'c' ],
            1 => [ 'a', 'b', 'c' ],
            'number' => [ '1', '2', '3' ],
            2 => [ '1', '2', '3' ],
            'both' =>[ 'a1', 'b2', 'c3' ],
            3 =>[ 'a1', 'b2', 'c3' ],
        ];
        
        $expected = [
            'count' => 3,
            'sub_count' => count($data),
            'sub_keys' => array_keys($data),
            'items' => [
                [
                    0 => '[a,1,a1]',
                    'letter' => 'a',
                    1 => 'a',
                    'number' => '1',
                    2 => '1',
                    'both' => 'a1',
                    3 => 'a1',
                ],
                [
                    0 => '[b,2,b2]',
                    'letter' => 'b',
                    1 => 'b',
                    'number' => '2',
                    2 => '2',
                    'both' => 'b2',
                    3 => 'b2',
                ],
                [
                    0 => '[c,3,c3]',
                    'letter' => 'c',
                    1 => 'c',
                    'number' => '3',
                    2 => '3',
                    'both' => 'c3',
                    3 => 'c3',
                ],    
            ]
        ];
        
        return [
            [
                [  /* first and only argument is an array */
                    'arguments' => [ $data ]
                ],
                $expected
            ]
        ];
    }
}
