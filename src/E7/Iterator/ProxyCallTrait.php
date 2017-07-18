<?php

namespace E7\Iterator;

trait ProxyCallTrait
{
    /**
     * @param   string $method
     * @param   array $args
     * @return  array
     * @throws  \UnexpectedValueException
     * @throws  \BadMethodCallException
     */
    public function __call($method, array $args)
    {
        $results = [];

        foreach ($this as $index => $object) {
            if (!is_object($object)) {
                throw new \UnexpectedValueException('Element is not an object!');
            }

            if (!method_exists($object, $method)
                && !method_exists($object, '__call')) {
                throw new \BadMethodCallException('Method ' . get_class($object) . '::' . $method . ' does not exist.');
            }

            $results[$index] = call_user_func_array([$object, $method], $args);
        }

        return $results;
    }
}