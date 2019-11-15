<?php

namespace E7\Iterator\Filter;

use FilterIterator;
use InvalidArgumentException;
use Iterator;

/**
 * InstanceOfFilterInterator
 */
class InstanceOfFilterInterator extends FilterIterator
{
    /** @var array */
    private $acceptedTypes = [];

    /**
     * @param Iterator $iterator
     * @param array|string $acceptedTypes
     * @throws InvalidArgumentException
     */
    public function __construct(Iterator $iterator, $acceptedTypes)
    {
        parent::__construct($iterator);
        
        if (is_string($acceptedTypes) && !empty($acceptedTypes)) {
            $acceptedTypes = array_map('trim', explode(',', $acceptedTypes));
        }
        
        if (!is_array($acceptedTypes)) {
            throw new InvalidArgumentException(
                'The Parameter $acceptedTypes must be an array, object or a comma separated string'
            );
        }
 
        $this->acceptedTypes = $acceptedTypes;
    }

    /**
     * @inheritDoc
     */
    public function accept(): bool
    {
        foreach ($this->acceptedTypes as $type) {
            if ($this->current() instanceof $type) {
                return true;
            }
        }

        return false;
    }
}
