<?php

namespace E7\Iterator;

use PHPUnit\Framework\TestCase;

class PermutationIteratorTest extends TestCase
{
    /**
     * @dataProvider    providerConstructor
     * @param   array $parameters
     * @param   array $expected
     */
    public function testConstructor(array $parameters, array $expected)
    {
        $reflection = new \ReflectionClass(PermutationIterator::class);
        $iterator = $reflection->newInstanceArgs($parameters);

        $countTotal = 0;

        foreach ($iterator as $key => $value) {
            $countTotal++;
        }

        $this->assertEquals($expected['count_total'], $countTotal);
    }

    /**
     * @return  array
     */
    public function providerConstructor()
    {
        $data = [
            [
                [
                    range(1, 5)
                ],
                [
                    'count_total' => 5
                ]
            ],
            [
                [
                    range(1, 3),
                    range(4, 6),
                    range(7, 9),
                ],
                [
                    'count_total' => 27
                ]
            ]
        ];

        // prepare same data with ArrayIterator
        foreach ($data as $testdata) {
            $newTestdata = $testdata;
            foreach($testdata[0] as $index => $value) {
                if (is_array($testdata[0][$index])) {
                    $newTestdata[0][$index] = new \ArrayIterator($testdata[0][$index]);
                }
            }
            $data[] = $newTestdata;
        }

        return $data;
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testConstructorWithException()
    {
        new PermutationIterator('this is not traversable and should cause an exception');
    }
}
