<?php
declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Models\Event;

use Spiral\Models\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Entity specific event.
 */
class EntityEvent extends Event
{
    /** @var EntityInterface */
    private $entity = null;

    /**
     * @param EntityInterface $entity
     */
    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }
}
