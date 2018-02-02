<?php

namespace E7\Date;

use PHPUnit\Framework\TestCase;
use E7\Utility\Range\RangeInterface;
use E7\Utility\Range\MergeableRangeInterface;

class DateRangeTest extends TestCase
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

    public function testGetInterval()
    {
        $range = new DateRange('2018-01-01', '2018-01-11');
        $interval = $range->getInterval();

        $this->assertInstanceOf(\DateInterval::class, $interval);
        $this->assertEquals(10, $interval->format('%a'));
    }

    public function testGetPeriod()
    {
        $range = new DateRange('2018-01-01', '2018-01-11');
        $period = $range->getPeriod();

        $count = 0;
        foreach ($period as $day) {
            $count++;
        }

        $this->assertInstanceOf(\DatePeriod::class, $period);
        $this->assertEquals(11, $count);
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
//    public function testGetDifference()
//    {
//        $range1 = new DateRange('2016-03-01', '2016-03-31');
//        $range2 = new DateRange('2016-03-11', '2016-03-15');
//                
//        $result = $range1->getDifference($range2);
//       
////        echo "\nIN $range1 $range2\n";
////        foreach ($result as $range) {
////            echo "$range\n";
////        }
//        
//        
//    }
    
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
        // test both ways, expect the same result
        $result1 = $input['range1']->merge($input['range2'], $input['type']);
        $this->assertEquals($expected, $result1);
        
        $result2 = $input['range2']->merge($input['range1'], $input['type']);
        $this->assertEquals($expected, $result2);
    }
    
    /**
     * @return  array
     */
    public function providerMerge()
    {
        return [
            [  /* range1 touches ranges2, expect merged range with type=touch */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-15'),
                    'range2' => new DateRange('2016-03-16', '2016-03-31'),
                    'type' => MergeableRangeInterface::MERGE_TOUCH,
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [  /* range1 touches ranges2, expect null with type=overlap */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-15'),
                    'range2' => new DateRange('2016-03-16', '2016-03-31'),
                    'type' => MergeableRangeInterface::MERGE_OVERLAP
                ],
                null
            ],
            [  /* range2 touches ranges1, expect merged range with type=touch */
                [
                    'range1' => new DateRange('2016-03-16', '2016-03-31'),
                    'range2' => new DateRange('2016-03-01', '2016-03-15'),
                    'type' => MergeableRangeInterface::MERGE_TOUCH,
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [  /* range2 touches ranges1, expect null with type=overlap */
                [
                    'range1' => new DateRange('2016-03-16', '2016-03-31'),
                    'range2' => new DateRange('2016-03-01', '2016-03-15'),
                    'type' => MergeableRangeInterface::MERGE_OVERLAP,
                ],
                null,
            ],
            [  /* range1 overlaps ranges2, expect merged range with type=touch */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-20'),
                    'range2' => new DateRange('2016-03-10', '2016-03-31'),
                    'type' => MergeableRangeInterface::MERGE_TOUCH,
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [  /* range1 overlaps ranges2, expect merged range type=overlap */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-20'),
                    'range2' => new DateRange('2016-03-10', '2016-03-31'),
                    'type' => MergeableRangeInterface::MERGE_OVERLAP,
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [  /* range1 is completely in ranges2, expect merged range type=touch */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-20'),
                    'range2' => new DateRange('2016-03-01', '2016-03-31'),
                    'type' => MergeableRangeInterface::MERGE_TOUCH,
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [  /* range1 is completely in ranges2, expect merged range type=overlap */
                [
                    'range1' => new DateRange('2016-03-10', '2016-03-20'),
                    'range2' => new DateRange('2016-03-01', '2016-03-31'),
                    'type' => MergeableRangeInterface::MERGE_OVERLAP,
                ],
                new DateRange('2016-03-01', '2016-03-31'),
            ],
            [  /* range1 and ranges2 are completely different, expect null with type=touch */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-10'),
                    'range2' => new DateRange('2016-03-20', '2016-03-30'),
                    'type' => MergeableRangeInterface::MERGE_TOUCH,
                ],
                null,
            ],
            [  /* range1 and ranges2 are completely different, expect null with type=overlap */
                [
                    'range1' => new DateRange('2016-03-01', '2016-03-10'),
                    'range2' => new DateRange('2016-03-20', '2016-03-30'),
                    'type' => MergeableRangeInterface::MERGE_OVERLAP,
                ],
                null,
            ],
        ];
    }
    
    public function testStaticCreate()
    {
        $from = '2016-03-01';
        $to = '2016-03-31';
        $value = 42;
        $options = ['foo' => 'bar'];
        
        $range = TestDateRange::create($from, $to, $value, $options);
        
        $this->assertInstanceOf(TestDateRange::class, $range);
        $this->assertEquals($from, $range->getFrom()->format('Y-m-d'));
        $this->assertEquals($to, $range->getTo()->format('Y-m-d'));
        $this->assertEquals($value, $range->getValue());
        $this->assertEquals($options, $range->getOptions());
    }
    
    public function testAfterMergeCallback()
    {
        $range1 = new TestDateRange('2018-01-10', '2018-01-20', 5);
        $range2 = new TestDateRange('2018-01-15', '2018-01-30', 10);
        
        $mergedRange = $range1->merge($range2);
        
        $this->assertNotSame($range1, $mergedRange);
        $this->assertNotSame($range2, $mergedRange);
        $this->assertInstanceOf(get_class($range1), $mergedRange);
        $this->assertEquals('2018-01-10', $mergedRange->getFrom()->format('Y-m-d'));
        $this->assertEquals('2018-01-30', $mergedRange->getTo()->format('Y-m-d'));
        $this->assertEquals(15, $mergedRange->getValue());
    }
    
}

// classes for test
class TestDateRange extends DateRange
{
    /** @var mixed */
    private $value;
    
    /**
     * Constructor
     * 
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param mixed $value
     * @param array $options
     */
    public function __construct($from, $to, $value, array $options = [])
    {
        parent::__construct($from, $to, $options);
        $this->value = $value;
    }

    /**
     * Set value
     * 
     * @param  mixed $value
     * @return TestDateRange
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    /**
     * Get value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
        
    /**
     * {@inheritDoc}
     */
    protected function afterMerge($range1, $range2)
    {
        $this->value = $range1->value + $range2->value;
    }
}