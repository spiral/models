<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Models\Event;

use Spiral\Models\Reflection\ReflectionEntity;
use Symfony\Component\EventDispatcher\Event;

/**
 * Raised while entity analysis to allow traits and other listeners apply changed to entity schema.
 */
class ReflectionEvent extends Event
{
    public const EVENT = 'describe';

    /** @var ReflectionEntity */
    private $reflection;

    /** @var string */
    private $property;

    /** @var mixed */
    private $value;

    /**
     * @param ReflectionEntity $reflection
     * @param string           $property
     * @param mixed            $value
     */
    public function __construct(ReflectionEntity $reflection, string $property, $value)
    {
        $this->reflection = $reflection;
        $this->property = $property;
        $this->value = $value;
    }

    /**
     * @return ReflectionEntity
     */
    public function getReflection(): ReflectionEntity
    {
        return $this->reflection;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Alter value.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
