<?php
declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Models\Exception;

/**
 * Errors raised by Entity logic in runtime.
 */
class EntityException extends \RuntimeException implements EntityExceptionInterface
{
}
