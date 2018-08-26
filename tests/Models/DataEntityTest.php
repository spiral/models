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
        $entity = new DataEntity(['a' => 'b']);
        $this->assertSame(['a' => 'b'], $entity->serializeValue());
    }

    public function testSerialize()
    {
        $data = ['a' => 123, 'b' => null, 'c' => 'test'];

        $entity = new DataEntity($data);
        $this->assertEquals($data, $entity->serializeValue());
    }
}
