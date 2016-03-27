<?php

namespace E7\Date;

use E7\Utility\RangeInterface;

class DateRangeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterfaces()
    {
        $range = new DateRange('now', 'now');
        $this->assertInstanceOf(RangeInterface::class, $range);
    }
    
    /**
     * @dataProvider    providerConstructorAndFromToNormalisation
     * @param   array $parameters
     * @param   array $expected
     */
    public function testConstructorAndFromToNormalisation(array $parameters, array $expected)
    {
        $range = new DateRange($parameters['from'], $parameters['to']);
        $format = 'Y-m-d H:i:s'; 
        
        $this->assertEquals($expected['from'], $range->getFrom()->format($format));
        $this->assertEquals($expected['to'], $range->getTo()->format($format));
    }
    
    /**
     * @return  array
     */
    public function providerConstructorAndFromToNormalisation()
    {
        return [
            [
                /* test with string and normal order */
                ['from' => '2016-03-25 20:20:00', 'to' => '2016-03-25 20:30:00'],
                ['from' => '2016-03-25 00:00:00', 'to' => '2016-03-25 23:59:59']
            ],
            [
                /* test with DateTime and normal order */
                [
                    'from' => new \DateTime('2016-03-25 20:20:00'),
                    'to' => new \DateTime('2016-03-25 20:30:00')
                ],
                ['from' => '2016-03-25 00:00:00', 'to' => '2016-03-25 23:59:59']
            ],
            [
                /* test with string and twisted order */
                ['from' => '2016-03-25 20:30:00', 'to' => '2016-03-25 20:20:00'],
                ['from' => '2016-03-25 00:00:00', 'to' => '2016-03-25 23:59:59']
            ],
            [
                /* test with DateTime and twisted order */
                [
                    'from' => new \DateTime('2016-03-25 20:30:00'),
                    'to' => new \DateTime('2016-03-25 20:20:00')
                ],
                ['from' => '2016-03-25 00:00:00', 'to' => '2016-03-25 23:59:59']
            ]
        ];
    }

    /**
     * @dataProvider    providerContains
     * @param   array $input
     * @param   boolean $expected
     */
    public function testContains(array $input, $expected)
    {
        extract($input);
        /* @var $range \E7\Date\DateRange */
        /* @var $value \DateTime */
        
        $result = $range->contains($value);
        
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return  array
     */
    public function providerContains()
    {
        $range = new DateRange('2016-03-01', '2016-03-31');
        
        return [
            /* one day before, expect false */
            [['range' => $range, 'value' => '2016-02-29'], false],
            [['range' => $range, 'value' => new \DateTime('2016-02-29')], false],
            /* first day, expect true*/
            [['range' => $range, 'value' => '2016-03-01'], true],
            [['range' => $range, 'value' => new \DateTime('2016-03-01')], true],
            /* somewhere in the middle, of course expect true*/
            [['range' => $range, 'value' => '2016-03-15'], true],
            [['range' => $range, 'value' => new \DateTime('2016-03-15')], true],
            /* last day, expect true*/
            [['range' => $range, 'value' => '2016-03-31'], true],
            [['range' => $range, 'value' => new \DateTime('2016-03-31')], true],
            /* one day after, expect false*/
            [['range' => $range, 'value' => '2016-04-01'], false],
            [['range' => $range, 'value' => new \DateTime('2016-04-01')], false],
        ];
    }
    
//    public function testEquals()
//    {
//    }
    
    /**
     * @dataProvider    providerCheckCollision
     * @param   array $input
     * @param   boolean $expected
     */
    public function testCheckCollision(array $input, $expected)
    {
        extract($input);
        /* @var $range1 \E7\Date\DateRange */
        /* @var $range2 \E7\Date\DateRange */
        
        // test both ways, expect the same result
        $result1 = $range1->checkCollision($range2);
        $this->assertInternalType('boolean', $result1);
        $this->assertEquals($expected, $result1, $range1 . ' ' . $range2);
        
        $result2 = $range2->checkCollision($range1);
        $this->assertInternalType('boolean', $result2);
        $this->assertEquals($expected, $result2, $range1 . ' ' . $range2);
    }
    
    /**
     * @return  array
     */
    public function providerCheckCollision()
    {
        return [
            [
                /* range1 before range2, no collision */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-20'),
                    'range2' => new DateRange('2016-03-21', '2016-03-30'),
                ],
                false
            ],
            [
                /* range2 before range1, no collision */
                [
                    'range1' => new DateRange('2016-03-21', '2016-03-30'),
                    'range2' => new DateRange('2016-03-10', '2016-03-20'),
                ],
                false
            ],
            [
                /* range1 is in range2, expect collision */
                [
                    'range1' => new DateRange('2016-03-15', '2016-03-30'),
                    'range2' => new DateRange('2016-03-10', '2016-03-20'),
                ],
                true
            ],
            [
                /* range2 is in range1, expect collision */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-20'),
                    'range2' => new DateRange('2016-03-15', '2016-03-30'),
                ],
                true
            ],
            [
                /* range1 is completely in range2, expect collision */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-20'),
                    'range2' => new DateRange('2016-03-01', '2016-03-30'),
                ],
                true
            ],
            [
                /* range2 is completely in range1, expect collision */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-30'),
                    'range2' => new DateRange('2016-03-10', '2016-03-20'),
                ],
                true
            ],
        ];
    }

    /**
     * @dataProvider    providerCheckTouch
     * @param   array $input
     * @param   boolean $expected
     */
    public function testCheckTouch(array $input, $expected)
    {
        extract($input);
        /* @var $range1 \E7\Date\DateRange */
        /* @var $range2 \E7\Date\DateRange */
        
        // test both ways, expect the same result
        $result1 = $range1->checkTouch($range2);
        $this->assertInternalType('boolean', $result1);
        $this->assertEquals((bool) $expected, $result1);
        
        $result2 = $range2->checkTouch($range1);
        $this->assertInternalType('boolean', $result2);
        $this->assertEquals((bool) $expected, $result2);
    }
    
    /**
     * @return  array
     */
    public function providerCheckTouch()
    {
        return [
            [
                /* range1 before range2 with a gap, no touch */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-15'),
                    'range2' => new DateRange('2016-03-20', '2016-03-30'),
                ],
                false
            ],
            [
                /* range2 before range1 with a gap, no touch */
                [
                    'range1' => new DateRange('2016-03-20', '2016-03-30'),
                    'range2' => new DateRange('2016-03-10', '2016-03-15'),
                ],
                false
            ],
            [
                /* range1 is in range2, expect touch */
                [
                    'range1' => new DateRange('2016-03-15', '2016-03-30'),
                    'range2' => new DateRange('2016-03-10', '2016-03-20'),
                ],
                true
            ],
            [
                /* range2 is in range1, expect touch */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-20'),
                    'range2' => new DateRange('2016-03-15', '2016-03-30'),
                ],
                true
            ],
            [
                /* range1 is close to range2, expect touch */
                [
                    'range1' => new DateRange('2016-03-15', '2016-03-20'),
                    'range2' => new DateRange('2016-03-21', '2016-03-30'),
                ],
                true
            ],
            [
                /* range2 is in range1, expect touch */
                [
                    'range1' => new DateRange('2016-03-21', '2016-03-30'),
                    'range2' => new DateRange('2016-03-15', '2016-03-20'),
                ],
                true
            ],
            [
                /* range1 is completely in ranges2, expect merged range */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-20'),
                    'range2' => new DateRange('2016-03-01', '2016-03-31'),
                ],
                true
            ],
            [
                /* range2 is completely in ranges1, expect merged range */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-31'),
                    'range2' => new DateRange('2016-03-10', '2016-03-20'),
                ],
                true,
            ],
        ];
    }
    
    /**
     * @dataProvider    providerGetIntersection
     * @param   array $input
     * @param   \E7\Date\DateRange|null $expected
     */
    public function testGetIntersection(array $input, DateRange $expected = null)
    {
        extract($input);
        /* @var $range1 \E7\Date\DateRange */
        /* @var $range2 \E7\Date\DateRange */
        
        // test both way, expect the same result
        $result1 = $range1->getIntersection($range2);
        $this->assertEquals($expected, $result1);
        
        $result2 = $range2->getIntersection($range1);
        $this->assertEquals($expected, $result2);
    }
    
    public function providerGetIntersection()
    {
        return [
            [
                /* same range */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-30'),
                    'range2' => new DateRange('2016-03-01', '2016-03-30'),
                ],
                new DateRange('2016-03-01', '2016-03-30')
            ],
            [
                /* same range (only one day range */
                [
                    'range1' => new DateRange('2016-03-25', '2016-03-25'),
                    'range2' => new DateRange('2016-03-25', '2016-03-25'),
                ],
                new DateRange('2016-03-25', '2016-03-25')
            ],
            [
                /* big intersection */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-20'),
                    'range2' => new DateRange('2016-03-10', '2016-03-30'),
                ],
                new DateRange('2016-03-10', '2016-03-20')
            ],
            [
                /* only on day intersection (last day range1/first day range 2) */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-15'),
                    'range2' => new DateRange('2016-03-15', '2016-03-30'),
                ],
                new DateRange('2016-03-15', '2016-03-15')
            ],
            [
                /* only on day intersection (first day range1/last day range 2) */
                [
                    'range1' => new DateRange('2016-03-15', '2016-03-30'),
                    'range2' => new DateRange('2016-03-01', '2016-03-15'),
                ],
                new DateRange('2016-03-15', '2016-03-15')
            ],
            [
                /* no intersection, range1 before range 2 */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-10'),
                    'range2' => new DateRange('2016-03-20', '2016-03-30'),
                ],
                null
            ],
            [
                /* no intersection, range1 after range 2 */
                [
                    'range1' => new DateRange('2016-03-20', '2016-03-30'),
                    'range2' => new DateRange('2016-03-01', '2016-03-10'),
                ],
                null
            ]
        ];
    }
    
    /**
     * @ dataProvider    providerGetDifference
     * @param   array $input
     * @param   array|null $expected
     */
    public function testGetDifference()
    {
        $range1 = new DateRange('2016-03-01', '2016-03-31');
        $range2 = new DateRange('2016-03-11', '2016-03-15');
                
        $result = $range1->getDifference($range2);
       
//        echo "\nIN $range1 $range2\n";
//        foreach ($result as $range) {
//            echo "$range\n";
//        }
        
        
    }
    
    /**
     * @return  array
     */
//    public function providerGetDifference()
//    {
//        return [
//            [
//                /* both ranges are equal, expect null */
//                [
//                    'range1' => new DateRange('2016-03-01', '2016-03-31'),
//                    'range2' => new DateRange('2016-03-01', '2016-03-31'),
//                ],
//                null
//            ],
//            [
//                /* range1 touch range2, expect the same ranges */
//                [
//                    'range1' => new DateRange('2016-03-01', '2016-03-31'),
//                    'range2' => new DateRange('2016-04-01', '2016-03-30'),
//                ],
//                [
//                    'range1' => new DateRange('2016-03-01', '2016-03-31'),
//                    'range2' => new DateRange('2016-04-01', '2016-03-30'),
//                ]
//            ],
//        ];
//    }
    
    /**
     * @dataProvider    providerMerge
     * @param   array $input
     * @param   \E7\Date\DateRange|null $expected
     */
    public function testMerge(array $input, DateRange $expected = null)
    {
        extract($input);
        /* @var $range1 \E7\Date\DateRange */
        /* @var $range2 \E7\Date\DateRange */
        
        // test both way, expect the same result
        $result1 = $range1->merge($range2);
        $this->assertEquals($expected, $result1);
        
        $result2 = $range2->merge($range1);
        $this->assertEquals($expected, $result2);
    }
    
    /**
     * @return  array
     */
    public function providerMerge()
    {
        return [
            [
                /* range1 touches ranges2, expect merged range */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-15'),
                    'range2' => new DateRange('2016-03-16', '2016-03-31'),
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [
                /* range2 touches ranges1, expect merged range */
                [
                    'range1' => new DateRange('2016-03-16', '2016-03-31'),
                    'range2' => new DateRange('2016-03-01', '2016-03-15'),
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [
                /* range1 overlaps ranges2, expect merged range */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-20'),
                    'range2' => new DateRange('2016-03-10', '2016-03-31'),
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [
                /* range2 touches ranges1, expect merged range */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-31'),
                    'range2' => new DateRange('2016-03-01', '2016-03-20'),
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [
                /* range1 is completely in ranges2, expect merged range */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-20'),
                    'range2' => new DateRange('2016-03-01', '2016-03-31'),
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [
                /* range2 is completely in ranges1, expect merged range */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-31'),
                    'range2' => new DateRange('2016-03-10', '2016-03-20'),
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
        ];
    }
    
    public function testStaticCreate()
    {
        $from = '2016-03-01';
        $to = '2016-03-31';
        
        $range = DateRange::create($from, $to);
        
        $this->assertInstanceOf(DateRange::class, $range);
        $this->assertEquals($from, $range->getFrom()->format('Y-m-d'));
        $this->assertEquals($to, $range->getTo()->format('Y-m-d'));
    }
}