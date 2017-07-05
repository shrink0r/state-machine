<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\StateTrait;

final class InteractiveState implements StateInterface
{
    use StateTrait;

    public function isInteractive(): bool
    {
        return true;
    }
}
