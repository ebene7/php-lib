E7 PHP Library
======

Installation
------------
The best way to install ebene7/php-lib is using  [Composer](http://getcomposer.org/):

```sh
$ composer require ebene7/php-lib
```

Date
----

* DateRange

Iterator
--------

* LeafIterator
* PermutationIterator
* ProxyCallIterator
* WalkableIterator

## Changes

### 1.1.0
* Merge calls now the method afterMerge($this, $range) to modify the payload
* Remove $class parameter from AbstractRange::create(), add optional parameter $options instead
* The values $lowerFrom and $higherTo now instanciate only once and stored in property in DateRange
* Add method getInterval() and getPeriod() to DateRange
* AbstractRange::create() can now handle flexible parameter lists
* AbstractIteratorDecorator inherits now from OuterIterator instead from Iterator
* Add MergeableRangeInterface and implements it in AbstractRange
* The merge methods can handle now two modes "touch" and "overlap"
* Change PHPUnit version and namespaces in testclasses
