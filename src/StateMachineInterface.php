<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine;

use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\StateMap;
use Daikon\StateMachine\Transition\StateTransitions;

interface StateMachineInterface
{
    public function getName(): string;

    public function getInitialState(): StateInterface;

    public function getFinalStates(): StateMap;

    public function getStates(): StateMap;

    public function getStateTransitions(): StateTransitions;

    public function execute(InputInterface $input, string $startState = null): OutputInterface;
}
