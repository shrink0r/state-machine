<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine;

use Daikon\StateMachine\Error\CorruptExecutionFlow;
use Daikon\StateMachine\Error\ExecutionError;
use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Param\InputInterface;
use Daikon\StateMachine\Param\Output;
use Daikon\StateMachine\Param\OutputInterface;
use Daikon\StateMachine\State\ExecutionTracker;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\StateMap;
use Daikon\StateMachine\State\StateSet;
use Daikon\StateMachine\Transition\StateTransitions;
use Daikon\StateMachine\Transition\TransitionSet;

final class StateMachine implements StateMachineInterface
{
    const MAX_CYCLES = 20;

    private $name;

    private $states;

    private $stateTransitions;

    private $initialState;

    private $finalStates;

    public function __construct(string $name, StateSet $stateSet, TransitionSet $transitionSet)
    {
        list($initial_state, $states, $final_states) = $stateSet->splat();
        $this->name = $name;
        $this->states = $states;
        $this->finalStates = $final_states;
        $this->initialState = $initial_state;
        $this->stateTransitions = new StateTransitions($states, $transitionSet);
    }

    public function execute(InputInterface $input, string $startState = null): OutputInterface
    {
        $executionTracker = new ExecutionTracker($this);
        $nextState = $this->determineStartState($input, $startState);
        do {
            $curCycle = $executionTracker->track($nextState);
            $output = $nextState->execute($input);
            if ($nextState->isInteractive()) {
                break;
            }
            $nextState = $this->activateTransition($input, $output);
            $input = Input::fromOutput($output);
        } while ($nextState && $curCycle < self::MAX_CYCLES);

        if ($nextState && $curCycle === self::MAX_CYCLES) {
            throw CorruptExecutionFlow::fromExecutionTracker($executionTracker, self::MAX_CYCLES);
        }
        return $output;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStates(): StateMap
    {
        return $this->states;
    }

    public function getInitialState(): StateInterface
    {
        return $this->initialState;
    }

    public function getFinalStates(): StateMap
    {
        return $this->finalStates;
    }

    public function getStateTransitions(): StateTransitions
    {
        return $this->stateTransitions;
    }

    private function determineStartState(InputInterface $input, string $stateName = null): StateInterface
    {
        if (!$stateName) {
            return $this->getInitialState();
        }
        if (!$this->states->has($stateName)) {
            throw new ExecutionError("Trying to start state-machine execution at unknown state: ".$stateName);
        }
        $startState = $this->states->get($stateName);
        if ($startState->isFinal()) {
            throw new ExecutionError("Trying to (re)execute state-machine at final state: ".$stateName);
        }
        if ($startState->isInteractive() && !$input->hasEvent()) {
            throw new ExecutionError("Trying to resume state-machine executing without providing an event/signal.");
        }
        return $startState->isInteractive()
            ? $this->activateTransition($input, Output::fromInput($startState->getName(), $input))
            : $startState;
    }

    private function activateTransition(InputInterface $input, OutputInterface $output)
    {
        $nextState = null;
        foreach ($this->stateTransitions->get($output->getCurrentState()) as $transition) {
            if ($transition->isActivatedBy($input, $output)) {
                if (is_null($nextState)) {
                    $nextState = $this->states->get($transition->getTo());
                    continue;
                }
                throw new ExecutionError(
                    'Trying to activate more than one transition at a time. Transition: '.
                    $output->getCurrentState().' -> '.$nextState->getName().' was activated first. '.
                    'Now '.$transition->getFrom().' -> '.$transition->getTo().' is being activated too.'
                );
            }
        }
        return $nextState;
    }
}
