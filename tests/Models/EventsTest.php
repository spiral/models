<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Models\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Models\DataEntity;
use Spiral\Models\Events\EntityEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventsTest extends TestCase
{
    public function testEventsDispatcher()
    {
        $this->assertInstanceOf(EventDispatcherInterface::class,
            EventsTestEntity::getEventDispatcher());
        $this->assertInstanceOf(EventDispatcherInterface::class, DataEntity::getEventDispatcher());
        $this->assertNotSame(EventsTestEntity::getEventDispatcher(),
            DataEntity::getEventDispatcher());

        $class = new EventsTestEntity();
        $this->assertSame(EventsTestEntity::getEventDispatcher(), $class->getEventDispatcher());
    }

    public function testSetEventsDispatcher()
    {
        $events = m::mock(EventDispatcherInterface::class);
        EventsTestEntity::setEventDispatcher($events);

        $this->assertSame($events, EventsTestEntity::getEventDispatcher());

        $class = new EventsTestEntity();
        $this->assertSame($events, $class->getEventDispatcher());

        EventsTestEntity::setEventDispatcher(null);

        $this->assertInstanceOf(EventDispatcherInterface::class, $class->getEventDispatcher());
        $this->assertNotSame($events, $class->getEventDispatcher());
    }

    public function testFireEvent()
    {
        $class = new EventsTestEntity();
        $this->assertInstanceOf(EntityEvent::class, $class->doSomething());
        $this->assertSame($class, $class->doSomething()->getEntity());
    }

    public function testFireEventNoDispatcher()
    {
        EventsTestEntity::setEventDispatcher(null);
        $class = new EventsTestEntity();
        EventsTestEntity::resetInitiated();

        $this->assertInstanceOf(EntityEvent::class, $class->doSomething());
    }
}

class EventsTestEntity extends DataEntity
{
    public function doSomething()
    {
        return $this->dispatch('test', new EntityEvent($this));
    }
}