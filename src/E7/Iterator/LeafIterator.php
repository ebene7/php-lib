<?php

namespace E7\Iterator;

/**
 * LeafIterator - Iterator for nested loops
 *
 * Example:
 *
 * foreach ($categories as $category) {
 *   foreach ($category->getPosts() as $post) {
 *     foreach ($post->getTags() as $tag) {
 *       echo $tag;
 *     }
 *   }
 * }
 *
 * With LeafIterator:
 *
 * $iterator = new LeafIterator($categories, 'getPosts().getTags()');
 *
 * foreach ($iterator as $tag) {
 *   echo $tag;
 * }
 *
 * -- OR --
 *
 * $iterator = new LeafIterator($categories, 'getPosts().getTags()', true);
 *
 * foreach ($iterator as $all) { // $all is an array with current objects
 *   foreach ($all as $object) {
 *     echo get_class($object) . PHP_EOL;
 *   }
 * }
 */
class LeafIterator implements \Iterator
{
    /**
     * @var array
     */
    private $inner = [];

    /**
     * @var array
     */
    private $path = [];

    /**
     * @var integer
     */
    private $maxIndex;

    /**
     * @var boolean
     */
    private $asArray = false;

    /**
     * Constructor
     *
     * @param   \Iterator|\IteratorAggregate|array $inner
     * @param   string $path
     * @param   boolean $asArray
     * @throws  \InvalidArgumentException
     */
    public function __construct($inner, $path = '', $asArray = false)
    {
        if (is_array($inner)) {
            $inner = new \ArrayIterator($inner);
        }

        if ($inner instanceof \IteratorAggregate) {
            $inner = $inner->getIterator();
        }

        if (!$inner instanceof \Iterator) {
            throw new \InvalidArgumentException('First parameter expected an iterator or array');
        }

        $this->inner[] = $inner;
        $this->path = !empty($path) ? explode('.', $path) : [];
        $this->maxIndex = count($this->path);
        $this->asArray = $asArray;
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        if ($this->asArray) {
            $values = [];
            foreach ($this->inner as $inner) {
                $values[] = $inner->current();
            }
            return $values;
        }

        return $this->getLeafIterator()->current();
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        if ($this->asArray) {
            $keys = [];
            foreach ($this->inner as $inner) {
                $keys[] = $inner->key();
            }
            return implode('-', $keys);
        }
        return $this->getLeafIterator()->key();
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        for ($i = $this->maxIndex; $i >= 0; $i--) {
            if (null === ($iterator = $this->getIteratorByIndex($i))) {
                continue;
            }

            $iterator->next();
            if ($iterator->valid()) {
                break;
            }

            unset($this->inner[$i]);
        }

        $this->makeBranch();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->inner[0]->rewind();
        $this->inner = [0 => $this->inner[0]];
        $this->makeBranch();
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        $it = $this->getLeafIterator();
        return $it instanceof \Iterator && $it->valid();
    }

    /**
     * @return  \Iterator|null
     */
    protected function getLeafIterator()
    {
        return $this->getIteratorByIndex($this->maxIndex);
    }

    /**
     * @return  boolean
     */
    protected function makeBranch()
    {
        $index = count($this->inner);  // the inner index we want to set

        do {
            if ($index > $this->maxIndex || $index < 1) {  // validation
                return false;
            }

            $previous = $this->getIteratorByIndex($index-1);
            if (!$previous instanceof \Iterator || !$previous->valid()) {
                if ($index-1 <= 0) {
                    return false;
                }
                unset($this->inner[$index-1]);
                $index--;
                continue;
            }

            $element = $previous->current();
            $iterator = $this->getIteratorFromElement($element, $this->path[$index-1]);

            if ($iterator instanceof \Iterator && $iterator->valid()) {
                $this->inner[$index] = $iterator;
                $index++;
                continue;
            } else {
                $prev = $this->getIteratorByIndex($index-1);
                $prev->next();
                if (!$prev->valid()) {
                    unset($this->inner[$index-1]);
                }
            }
        } while($index <= $this->maxIndex);

        return false;
    }

    /**
     * @param   index $index
     * @return  \Iterator|null
     */
    protected function getIteratorByIndex($index)
    {
        return !empty($this->inner[$index]) && $this->inner[$index] instanceof \Iterator ?
            $this->inner[$index] : null;
    }

    /**
     * @param   index $index
     * @return  \Iterator|null
     */
    protected function getElementByIndex($index)
    {
        return null !== ($iterator = $this->getIteratorByIndex($index)) ?
            $iterator->current() : null;
    }

    /**
     * @param   \Iterator $element
     * @param   string $field
     * @return  \Iterator
     * @throws  \Exception
     */
    protected function getIteratorFromElement($element, $field)
    {
        $iterator = null;

        // array access
        if (preg_match('/\[(?P<name>.+)\]/', $field, $match)
            && !empty($element[$match['name']])) {
            $iterator = $element[$match['name']];
        } else
        // property access
        if (preg_match('/(?P<name>[a-zA-Z0-9_]+)/', $field, $match)
            && !empty($element->{$match['name']})) {
            $iterator = $element->{$match['name']};
        } else
        // method access
        if (preg_match('/(?P<name>[a-zA-Z0-9_]+)\(\)/', $field, $match)
            && method_exists($element, $match['name'])) {
            $iterator = call_user_func([$element, $match['name']]);
        }
        else
        // IteratorAggregate access
        if ($element instanceof \IteratorAggregate) {
            $iterator = $element->getIterator();
        }

        if ($iterator instanceof \IteratorAggregate) {
            $iterator = $iterator->getIterator();
        }

        if (null === $iterator || !$iterator instanceof \Iterator) {
            throw new \Exception('Impossible to find an iterator');
        }

        return $iterator;
    }
}