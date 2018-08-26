<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Models\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Models\DataEntity;

class DataEntityTest extends TestCase
{
    public function testSetter()
    {
        $entity = new DataEntity();
        $entity->setField('abc', 123);
        $this->assertEquals(123, $entity->getField('abc'));

        $this->assertTrue($entity->hasField('abc'));
        $this->assertFalse($entity->hasField('bce'));
    }

    public function testMagicProperties()
    {
        $entity = new DataEntity();
        $entity->abc = 123;
        $this->assertEquals(123, $entity->abc);

        $this->assertTrue(isset($entity->abc));
    }

    public function testPackingSimple()
    {
        $entity = new DataEntity(['a' => 'b', 'c' => 10]);
        $this->assertSame(['a' => 'b', 'c' => 10], $entity->packValue());
    }

    public function testSerialize()
    {
        $data = ['a' => 123, 'b' => null, 'c' => 'test'];

        $entity = new DataEntity($data);
        $this->assertEquals($data, $entity->packValue());
    }

    public function testSetValue()
    {
        $data = ['a' => 123, 'b' => null, 'c' => 'test'];

        $entity = new PublicEntity($data);
        $this->assertEquals($data, $entity->packValue());

        $entity = new PublicEntity();
        $entity->setValue(['a' => 123]);
        $this->assertEquals(['a' => 123], $entity->packValue());

        $this->assertSame(['a'], $entity->getKeys());
        $this->assertTrue(isset($entity->a));

        unset($entity->a);
        $this->assertEquals([], $entity->packValue());

        $entity['a'] = 90;
        $this->assertEquals(['a' => 90], $entity->packValue());
        $this->assertSame(90, $entity['a']);
        unset($entity['a']);
        $this->assertEquals([], $entity->packValue());
    }
}

class PublicEntity extends DataEntity
{
    protected const FILLABLE = '*';

    public function getKeys(): array
    {
        return parent::getKeys();
    }
}