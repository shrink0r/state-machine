<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Error;

use RuntimeException;
use Daikon\StateMachine\Error\ErrorInterface;

class MissingImplementation extends RuntimeException implements ErrorInterface
{

}
