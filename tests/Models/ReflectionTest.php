<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Models\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Models\Events\ReflectionEvent;
use Spiral\Models\Reflections\ReflectionEntity;
use Spiral\Models\SchematicEntity;

class ReflectionTest extends TestCase
{
    public function testReflection()
    {
        $schema = new ReflectionEntity(TestModel::class);
        $this->assertEquals(new \ReflectionClass(TestModel::class), $schema->getReflection());
    }

    public function testFillable()
    {
        $schema = new ReflectionEntity(TestModel::class);
        $this->assertSame(['value'], $schema->getFillable());
    }

    public function testFillableExtended()
    {
        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertSame(['value', 'name'], $schema->getFillable());
    }

    public function testSetters()
    {
        $schema = new ReflectionEntity(TestModel::class);
        $this->assertSame(
            [
                'value' => 'intval'
            ],
            $schema->getSetters()
        );
    }

    public function testSettersExtended()
    {
        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertSame(
            [
                'value' => 'intval',
                'name'  => 'strval'
            ],
            $schema->getSetters()
        );
    }

    public function testSecured()
    {
        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertSame(['name'], $schema->getSecured());
    }

    public function testDeclaredMethods()
    {
        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertEquals(
            [
                new \ReflectionMethod(ExtendedModel::class, 'methodB')
            ],
            $schema->declaredMethods()
        );
    }

    public function testEvents()
    {
        ExtendedModel::getEventDispatcher()->addListener(
            ReflectionEvent::EVENT,
            function (ReflectionEvent $e) {
                $this->assertSame('fillable', $e->getProperty());
                $this->assertSame(['value', 'name'], $e->getValue());
                $this->assertSame(ExtendedModel::class, $e->getReflection()->getName());
                $e->setValue(['value', 'name', 'other']);
            }
        );

        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertSame(['value', 'name', 'other'], $schema->getFillable());

        ExtendedModel::setEventDispatcher(null);
    }
}

class TestModel extends SchematicEntity
{
    protected $fillable = ['value'];
    protected $setters = ['value' => 'intval'];
    protected $getters = ['value' => 'intval'];
    protected $secured = '*';

    protected function methodA()
    {

    }
}

class ExtendedModel extends TestModel
{
    protected $fillable = ['name'];
    protected $setters = ['name' => 'strval'];
    protected $getters = ['name' => 'strtoupper'];
    protected $secured = ['name'];

    protected function methodB()
    {

    }
}