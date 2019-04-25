<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Models\Reflection;

use Spiral\Models\AbstractEntity;
use Spiral\Models\SchematicEntity;

/**
 * Provides ability to generate entity schema based on given entity class and default property
 * values, support value inheritance!
 *
 * @method bool isAbstract()
 * @method string getName()
 * @method string getShortName()
 * @method bool isSubclassOf($class)
 * @method bool hasConstant($name)
 * @method mixed getConstant($name)
 * @method \ReflectionMethod[] getMethods()
 * @method \ReflectionClass|null getParentClass()
 */
class ReflectionEntity
{
    /**
     * Required to validly merge parent and children attributes.
     */
    protected const BASE_CLASS = AbstractEntity::class;

    /**
     * Accessors and filters.
     */
    private const MUTATOR_GETTER   = 'getter';
    private const MUTATOR_SETTER   = 'setter';
    private const MUTATOR_ACCESSOR = 'accessor';

    /**
     * Properties cache.
     *
     * @invisible
     * @var array
     */
    private $propertyCache = [];

    /** @var \ReflectionClass */
    private $reflection = null;

    /**
     * Only support SchematicEntity classes!
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->reflection = new \ReflectionClass($class);
    }

    /**
     * @return \ReflectionClass
     */
    public function getReflection(): \ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return array|string
     */
    public function getSecured()
    {
        if ($this->getProperty('secured', true) === '*') {
            return $this->getProperty('secured', true);
        }

        return array_unique((array)$this->getProperty('secured', true));
    }

    /**
     * @return array
     */
    public function getFillable(): array
    {
        return array_unique((array)$this->getProperty('fillable', true));
    }

    /**
     * @return array
     */
    public function getSetters(): array
    {
        return $this->getMutators()[self::MUTATOR_SETTER];
    }

    /**
     * @return array
     */
    public function getGetters(): array
    {
        return $this->getMutators()[self::MUTATOR_GETTER];
    }

    /**
     * @return array
     */
    public function getAccessors(): array
    {
        return $this->getMutators()[self::MUTATOR_ACCESSOR];
    }

    /**
     * Get methods declared in current class and exclude methods declared in parents.
     *
     * @return \ReflectionMethod[]
     */
    public function declaredMethods(): array
    {
        $methods = [];
        foreach ($this->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() != $this->getName()) {
                continue;
            }

            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * Entity schema.
     *
     * @return array
     */
    public function getSchema(): array
    {
        //Default property to store schema
        return (array)$this->getProperty('schema', true);
    }

    /**
     * Model mutators grouped by their type.
     *
     * @return array
     */
    public function getMutators(): array
    {
        $mutators = [
            self::MUTATOR_GETTER   => [],
            self::MUTATOR_SETTER   => [],
            self::MUTATOR_ACCESSOR => [],
        ];

        foreach ((array)$this->getProperty('getters', true) as $field => $filter) {
            $mutators[self::MUTATOR_GETTER][$field] = $filter;
        }

        foreach ((array)$this->getProperty('setters', true) as $field => $filter) {
            $mutators[self::MUTATOR_SETTER][$field] = $filter;
        }

        foreach ((array)$this->getProperty('accessors', true) as $field => $filter) {
            $mutators[self::MUTATOR_ACCESSOR][$field] = $filter;
        }

        return $mutators;
    }

    /**
     * Read default model property value, will read "protected" and "private" properties. Method
     * raises entity event "describe" to allow it traits modify needed values.
     *
     * @param string $property Property name.
     * @param bool   $merge    If true value will be merged with all parent declarations.
     *
     * @return mixed
     */
    public function getProperty(string $property, bool $merge = false)
    {
        if (isset($this->propertyCache[$property])) {
            //Property merging and trait events are pretty slow
            return $this->propertyCache[$property];
        }

        $properties = $this->reflection->getDefaultProperties();
        $constants = $this->reflection->getConstants();

        if (isset($properties[$property])) {
            //Read from default value
            $value = $properties[$property];
        } elseif (isset($constants[strtoupper($property)])) {
            //Read from a constant
            $value = $constants[strtoupper($property)];
        } else {
            return null;
        }

        //Merge with parent value requested
        if ($merge && is_array($value) && !empty($parent = $this->parentReflection())) {
            $parentValue = $parent->getProperty($property, $merge);

            if (is_array($parentValue)) {
                //Class values prior to parent values
                $value = array_merge($parentValue, $value);
            }
        }

        if (!$this->reflection->isSubclassOf(SchematicEntity::class)) {
            return $value;
        }

        //To let traits apply schema changes
        return $this->propertyCache[$property] = call_user_func(
            [$this->getName(), 'describeProperty'], $this, $property, $value
        );
    }

    /**
     * Parent entity schema/
     *
     * @return ReflectionEntity|null
     */
    public function parentReflection()
    {
        $parentClass = $this->reflection->getParentClass();

        if (!empty($parentClass) && $parentClass->getName() != static::BASE_CLASS) {
            $parent = clone $this;
            $parent->reflection = $this->getParentClass();

            return $parent;
        }

        return null;
    }

    /**
     * Bypassing call to reflection.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->reflection, $name], $arguments);
    }


    /**
     * Cloning and flushing cache.
     */
    public function __clone()
    {
        $this->propertyCache = [];
    }
}