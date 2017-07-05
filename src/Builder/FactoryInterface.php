<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Builder;

use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\Transition\TransitionInterface;

interface FactoryInterface
{
    public function createState(string $name, array $state = null): StateInterface;

    public function createTransition(string $from, string $to, array $config = null): TransitionInterface;
}
