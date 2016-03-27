<?php

namespace E7\Iterator;

class WalkableIterator extends AbstractIteratorDecorator 
    implements WalkableIteratorInterface
{
    use WalkableIteratorEachTrait;
    use WalkableIteratorWalkTrait;
}

