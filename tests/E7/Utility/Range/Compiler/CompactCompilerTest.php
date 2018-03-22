<?php

namespace E7\Utility\Range\Compiler;

use E7\Utility\Range\NumberRange;

class CompactCompilerTest extends CompilerTestCase
{
    /**
     * @dataProvider providerCompile
     * @param        array $input
     * @param        array $expected
     */
    public function testCompile(array $input, array $expected)
    {
        $compiler = new CompactCompiler();

        $ranges = $compiler->compile($input['ranges']);

        $this->assertCount(count($expected['ranges']), $ranges);

        foreach ($ranges as $index => $range) {
            $this->assertEquals($expected['ranges'][$index], (string) $range);
        }
    }

    /**
     * @return array
     */
    public function providerCompile()
    {
        $numbers = [13, 14, 15, 16, 17, 18, 19, 20, 1, 3, 4, 25, 7, 8, 9, 10, 11, 21, 22, 23, 24];

        foreach ($numbers as $number) {
            $numberRanges[] = NumberRange::create($number);
            $dateRanges[] = \E7\Date\DateRange::create("2018-03-$number");
        }

        return [
            [
                [
                    'ranges' => $numberRanges
                ],
                [
                    'ranges' => [ '[13-25]', '[1-1]', '[3-4]', '[7-11]' ]
                ],
            ],
            [
                [
                    'ranges' => $dateRanges
                ],
                [
                    'ranges' => [
                        '[2018-03-13-2018-03-25]',
                        '[2018-03-01-2018-03-01]',
                        '[2018-03-03-2018-03-04]',
                        '[2018-03-07-2018-03-11]',
                    ]
                ],
            ],
        ];
    }
}
