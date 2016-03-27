<?php

namespace E7\Iterator;

class LeafIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIterface()
    {
        $iterator = new LeafIterator([]);
        $this->assertInstanceOf(\Iterator::class, $iterator);
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testConstructorWithException()
    {
        new LeafIterator('IAmNotAnArrayOrIterator');
    }

    public function testWithArray()
    {
        $values = [1,2,3];
        $iterator = new LeafIterator($values);
        $count = 0;

        foreach ($iterator as $leaf) {
            if (!in_array($leaf, $values)) {
                $this->fail('LeafIterator::current() returns unexpected value');
            }
            $count++;
        }

        $this->assertEquals(count($values), $count);
    }

    public function testCategoryPostTag()
    {
        $categoryCollection = $this->prepareCategoryCollection();
        $iterator = new LeafIterator($categoryCollection, 'getPosts().getTags()');

        // collect value to test
        $count = 0;

        foreach ($iterator as $tag) {
            if (!$tag instanceof Tag) {
                $this->fail('LeafIterator::current() returns unexpected value');
            }
            $count++;
        }
        $this->assertEquals(125, $count);
    }

    public function testCategoryPostTagWithAsArrayFlag()
    {
        $categoryCollection = $this->prepareCategoryCollection();
        $iterator = new LeafIterator($categoryCollection, 'getPosts().getTags()', true);

        // collect value to test
        $count = 0;
        $ids = [];

        foreach ($iterator as $allCurrent) {
            if (!is_array($allCurrent)) {
                $this->fail('LeafIterator::current() returns unexpected value');
            }

            $this->assertCount(3, $allCurrent);
            $this->assertInstanceOf(Category::class, $allCurrent[0]);
            $this->assertInstanceOf(Post::class, $allCurrent[1]);
            $this->assertInstanceOf(Tag::class, $allCurrent[2]);

            $count++;
            $ids['category'][] = $allCurrent[0]->getId();
            $ids['post'][] = $allCurrent[1]->getId();
            $ids['tag'][] = $allCurrent[2]->getId();
        }

        $this->assertEquals(125, $count);
        $this->assertCount(5, array_unique($ids['category']));
        $this->assertCount(25, array_unique($ids['post']));
        $this->assertCount(125, array_unique($ids['tag']));
    }

    /**
     * Returns a prefilled CategoryCollection
     *
     * @return  \BFewo\Iterator\CategoryCollection
     */
    protected function prepareCategoryCollection()
    {
        $categoryCollection = new CategoryCollection();

        for ($c=0; $c<5; $c++) {
            $postCollection = new PostCollection();
            $category = new Category($postCollection);
            $categoryCollection->append($category);

            for ($p=0; $p<5; $p++) {
                $tagCollection = new TagCollection();
                $post = new Post($tagCollection);
                $postCollection->append($post);

                for ($t=0; $t<5; $t++) {
                    $tag = new Tag();
                    $tagCollection->append($tag);
                }
            }
        }

        return $categoryCollection;
    }
}

// classes for test
class Entity {
    static $id;
    private $myId;
    private $iterator;
    public function __construct($it = null) { $this->myId = static::$id++; $this->iterator = $it; }
    public function __toString() { return __CLASS__ . ' ' . $this->myId; }
    public function getId() { return $this->myId; }
    public function setIterator(\Iterator $iterator) { $this->iterator = $iterator; }
    public function getIterator() { return $this->iterator; }
}
class Category extends Entity {
    public function getPosts() { return $this->getIterator(); }
}
class Post extends Entity {
    public function getTags() { return $this->getIterator(); }
}
class Tag extends Entity {}

class CategoryCollection extends \ArrayIterator {}
class PostCollection extends \ArrayIterator {}
class TagCollection extends \ArrayIterator {}

