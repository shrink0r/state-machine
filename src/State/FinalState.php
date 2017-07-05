<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

final class FinalState implements StateInterface
{
    use StateTrait;

    public function isFinal(): bool
    {
        return true;
    }
}
