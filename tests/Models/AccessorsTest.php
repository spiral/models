<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Models\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Models\AccessorInterface;
use Spiral\Models\DataEntity;
use Spiral\Models\Reflections\ReflectionEntity;

class AccessorsTest extends TestCase
{
    public function testAccessed()
    {
        $e = new AccessedEntity();
        $e->name = 'antony';
        $this->assertSame('ANTONY', (string)$e->name);

        $e->setFields(['name' => 'bob']);
        $this->assertSame('BOB', (string)$e->name);

        $this->assertSame([
            'name' => 'BOB'
        ], $e->packValue());

        $this->assertSame([
            'name' => 'BOB'
        ], $e->jsonSerialize());

        $this->assertEquals([
            'name' => new NameAccessor('bob')
        ], $e->getFields());

        $e->name = new NameAccessor("mike");

        $this->assertEquals([
            'name' => new NameAccessor('mike')
        ], $e->getFields());
    }

    public function testGetAccessor()
    {
        $e = new AccessedEntity();
        $this->assertSame('', (string)$e->name);
        $this->assertInstanceOf(NameAccessor::class, $e->name);

        $this->assertEquals([
            'name' => new NameAccessor(null)
        ], $e->getFields());

        $e->setFields(null);
    }

    public function testReflection()
    {
        $s = new ReflectionEntity(AccessedEntity::class);
        $this->assertSame([
            'name' => NameAccessor::class
        ], $s->getAccessors());
    }

    /**
     * @expectedException \Spiral\Models\Exceptions\EntityException
     */
    public function testException()
    {
        $e = new BadAccessedEntity();
        $e->name = "xx";
    }
}

class AccessedEntity extends DataEntity
{
    protected const FILLABLE  = '*';
    protected const ACCESSORS = [
        'name' => NameAccessor::class
    ];
}

class BadAccessedEntity extends DataEntity
{
    protected const FILLABLE  = '*';
    protected const ACCESSORS = [
        'name' => NameAccessorX::class
    ];
}

class NameAccessor implements AccessorInterface
{
    private $value;

    public function __construct($value)
    {
        $this->setValue($value);
    }

    public function setValue($data)
    {
        $this->value = strtoupper($data);
    }

    public function packValue()
    {
        return $this->value;
    }


    public function jsonSerialize()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}