<?php

namespace E7\Utility\Range;

class Normalizer
{
    public function normalize($ranges)
    {
        $norm = [];

        foreach ($ranges as $range) {
            $key = $range->getFrom()->format('Y-m-d') . '|' . $range->getTo()->format('Y-m-d');
            $norm[$key] = $range;
        }

        ksort($norm);

        return array_values($norm);
    }
}

