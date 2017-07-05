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
use Daikon\StateMachine\StateMachineInterface;
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

    private $state_transitions;

    private $initial_state;

    private $final_states;

    public function __construct(string $name, StateSet $state_set, TransitionSet $transition_set)
    {
        list($initial_state, $states, $final_states) = $state_set->splat();
        $this->name = $name;
        $this->states = $states;
        $this->final_states = $final_states;
        $this->initial_state = $initial_state;
        $this->state_transitions = new StateTransitions($states, $transition_set);
    }

    public function execute(InputInterface $input, string $start_state = null): OutputInterface
    {
        $execution_tracker = new ExecutionTracker($this);
        $next_state = $this->determineStartState($input, $start_state);
        do {
            $cur_cycle = $execution_tracker->track($next_state);
            $output = $next_state->execute($input);
            if ($next_state->isInteractive()) {
                break;
            }
            $next_state = $this->activateTransition($input, $output);
            $input = Input::fromOutput($output);
        } while ($next_state && $cur_cycle < self::MAX_CYCLES);

        if ($next_state && $cur_cycle === self::MAX_CYCLES) {
            throw CorruptExecutionFlow::fromExecutionTracker($execution_tracker, self::MAX_CYCLES);
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
        return $this->initial_state;
    }

    public function getFinalStates(): StateMap
    {
        return $this->final_states;
    }

    public function getStateTransitions(): StateTransitions
    {
        return $this->state_transitions;
    }

    private function determineStartState(InputInterface $input, string $state_name = null): StateInterface
    {
        if (!$state_name) {
            return $this->getInitialState();
        }
        if (!$this->states->has($state_name)) {
            throw new ExecutionError("Trying to start statemachine execution at unknown state: ".$state_name);
        }
        $start_state = $this->states->get($state_name);
        if ($start_state->isFinal()) {
            throw new ExecutionError("Trying to (re)execute statemachine at final state: ".$state_name);
        }
        if ($start_state->isInteractive() && !$input->hasEvent()) {
            throw new ExecutionError("Trying to resume statemachine executing without providing an event/signal.");
        }
        return $start_state->isInteractive()
            ? $this->activateTransition($input, Output::fromInput($start_state->getName(), $input))
            : $start_state;
    }

    private function activateTransition(InputInterface $input, OutputInterface $output)
    {
        $next_state = null;
        foreach ($this->state_transitions->get($output->getCurrentState()) as $transition) {
            if ($transition->isActivatedBy($input, $output)) {
                if (is_null($next_state)) {
                    $next_state = $this->states->get($transition->getTo());
                    continue;
                }
                throw new ExecutionError(
                    'Trying to activate more than one transition at a time. Transition: '.
                    $output->getCurrentState().' -> '.$next_state->getName().' was activated first. '.
                    'Now '.$transition->getFrom().' -> '.$transition->getTo().' is being activated too.'
                );
            }
        }
        return $next_state;
    }
}
