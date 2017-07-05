<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Builder;

use Ds\Map;
use Daikon\StateMachine\Builder\StateMachineBuilderInterface;
use Daikon\StateMachine\Error\InvalidStructure;
use Daikon\StateMachine\Error\MissingImplementation;
use Daikon\StateMachine\Error\UnknownState;
use Daikon\StateMachine\StateMachineInterface;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\StateSet;
use Daikon\StateMachine\Transition\TransitionInterface;
use Daikon\StateMachine\Transition\TransitionSet;

final class StateMachineBuilder implements StateMachineBuilderInterface
{
    private $states;

    private $transitions;

    private $state_machine_name;

    private $state_machine_class;

    public function __construct(string $state_machine_class)
    {
        $this->states = new Map;
        $this->transitions = new Map;
        $this->state_machine_class = $state_machine_class;
        if (!class_exists($this->state_machine_class)) {
            throw new MissingImplementation('Trying to create statemachine from non-existant class.');
        }
        if (!in_array(StateMachineInterface::CLASS, class_implements($this->state_machine_class))) {
            throw new MissingImplementation(
                'Trying to build statemachine that does not implement required '.StateMachineInterface::CLASS
            );
        }
    }

    public function build(): StateMachineInterface
    {
        $states = new StateSet($this->states->values()->toArray());
        $transitions = new TransitionSet($this->transitions->values()->toArray());
        return new $this->state_machine_class($this->state_machine_name, $states, $transitions);
    }

    public function addStateMachineName(string $state_machine_name): self
    {
        $builder = clone $this;
        $builder->state_machine_name = $state_machine_name;
        return $builder;
    }

    public function addState(StateInterface $state): self
    {
        $builder = clone $this;
        $builder->states[$state->getName()] = $state;
        return $builder;
    }

    public function addStates(array $states): self
    {
        $builder = clone $this;
        foreach ($states as $state) {
            $builder->states[$state->getName()] = $state;
        }
        return $builder;
    }

    public function addTransition(TransitionInterface $transition): self
    {
        if (!$this->states->hasKey($transition->getFrom())) {
            throw new UnknownState('Trying to add transition from unknown state: ' . $transition->getFrom());
        }
        if (!$this->states->hasKey($transition->getTo())) {
            throw new UnknownState('Trying to add transition to unknown state: ' . $transition->getTo());
        }
        $transition_key = $transition->getFrom().$transition->getTo();
        if ($this->transitions->hasKey($transition_key)) {
            throw new InvalidStructure(
                sprintf('Trying to add same transition twice: %s -> %s', $transition->getFrom(), $transition->getTo())
            );
        }
        $builder = clone $this;
        $builder->transitions[$transition_key] = $transition;
        return $builder;
    }

    public function addTransitions(array $transitions): self
    {
        $builder = clone $this;
        foreach ($transitions as $transition) {
            $builder = $this->addTransition($transition);
        }
        return $builder;
    }
}
