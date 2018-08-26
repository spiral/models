<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Models;

use Spiral\Models\Events\ReflectionEvent;
use Spiral\Models\Reflections\ReflectionEntity;

/**
 * Entity which code follows external behaviour schema.
 */
class SchematicEntity extends AbstractEntity
{
    /**
     * Schema constants. Starts with 2, but why not?
     */
    const SH_SECURED  = 2;
    const SH_FILLABLE = 3;
    const SH_MUTATORS = 4;

    /**
     * Behaviour schema.
     *
     * @var array
     */
    private $schema = [];

    /**
     * @param array $data
     * @param array $schema
     */
    public function __construct(array $data, array $schema)
    {
        $this->schema = $schema;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function isFillable(string $field): bool
    {
        if (!empty($this->schema[self::SH_FILLABLE]) && $this->schema[self::SH_FILLABLE] === '*') {
            return true;
        }

        if (!empty($this->schema[self::SH_FILLABLE])) {
            return in_array($field, $this->schema[self::SH_FILLABLE]);
        }

        if (!empty($this->schema[self::SH_SECURED]) && $this->schema[self::SH_SECURED] === '*') {
            return false;
        }

        return !in_array($field, $this->schema[self::SH_SECURED]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMutator(string $field, string $mutator)
    {
        if (isset($this->schema[self::SH_MUTATORS][$mutator][$field])) {
            return $this->schema[self::SH_MUTATORS][$mutator][$field];
        }

        return null;
    }

    /**
     * Method used while entity static analysis to describe model related property using even
     * dispatcher and associated model traits.
     *
     * @param ReflectionEntity $reflection
     * @param string           $property
     * @param mixed            $value
     *
     * @return mixed Returns filtered value.
     * @event describe(DescribeEvent)
     */
    public static function describeProperty(ReflectionEntity $reflection, string $property, $value)
    {
        static::initialize(true);

        /**
         * Clarifying property value using traits or other listeners.
         *
         * @var ReflectionEvent $event
         */
        $event = static::getEventDispatcher()->dispatch(
            'describe',
            new ReflectionEvent($reflection, $property, $value)
        );

        return $event->getValue();
    }
}