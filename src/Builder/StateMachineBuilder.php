<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Builder;

use Daikon\StateMachine\Exception\InvalidStructure;
use Daikon\StateMachine\Exception\MissingImplementation;
use Daikon\StateMachine\Exception\UnknownState;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\StateSet;
use Daikon\StateMachine\StateMachineInterface;
use Daikon\StateMachine\Transition\TransitionInterface;
use Daikon\StateMachine\Transition\TransitionSet;
use Ds\Map;

final class StateMachineBuilder implements StateMachineBuilderInterface
{
    private $states;

    private $transitions;

    private $stateMachineName;

    private $stateMachineClass;

    public function __construct(string $stateMachineClass)
    {
        $this->states = new Map;
        $this->transitions = new Map;
        $this->stateMachineClass = $stateMachineClass;
        if (!class_exists($this->stateMachineClass)) {
            throw new MissingImplementation('Trying to create state-machine from non-existent class.');
        }
        if (!in_array(StateMachineInterface::CLASS, class_implements($this->stateMachineClass))) {
            throw new MissingImplementation(
                'Trying to build state-machine that does not implement required '.StateMachineInterface::CLASS
            );
        }
    }

    public function build(): StateMachineInterface
    {
        $states = new StateSet($this->states->values()->toArray());
        $transitions = new TransitionSet($this->transitions->values()->toArray());
        return new $this->stateMachineClass($this->stateMachineName, $states, $transitions);
    }

    public function addStateMachineName(string $state_machine_name): self
    {
        $builder = clone $this;
        $builder->stateMachineName = $state_machine_name;
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
        $transitionKey = $transition->getFrom().$transition->getTo();
        if ($this->transitions->hasKey($transitionKey)) {
            throw new InvalidStructure(
                sprintf('Trying to add same transition twice: %s -> %s', $transition->getFrom(), $transition->getTo())
            );
        }
        $builder = clone $this;
        $builder->transitions[$transitionKey] = $transition;
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
