<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Transition;

use Countable;
use Daikon\StateMachine\Error\InvalidStructure;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\StateMap;
use Daikon\StateMachine\State\StateSet;
use Ds\Map;
use IteratorAggregate;
use Traversable;

final class StateTransitions implements IteratorAggregate, Countable
{
    private $internalMap;

    public function __construct(StateMap $states, TransitionSet $transitions)
    {
        $this->internalMap = new Map;
        foreach ($transitions as $transition) {
            $fromState = $transition->getFrom();
            $toState = $transition->getTo();
            if (!$states->has($fromState)) {
                throw new InvalidStructure('Trying to transition from unknown state: '.$fromState);
            }
            if ($states->get($fromState)->isFinal()) {
                throw new InvalidStructure('Trying to transition from final-state: '.$fromState);
            }
            if (!$states->has($toState)) {
                throw new InvalidStructure('Trying to transition to unknown state: '.$toState);
            }
            if ($states->get($toState)->isInitial()) {
                throw new InvalidStructure('Trying to transition to initial-state: '.$toState);
            }
            $stateTransitions = $this->internalMap->get($transition->getFrom(), new TransitionSet);
            $this->internalMap->put($transition->getFrom(), $stateTransitions->add($transition));
        }
        $initialState = $states->findOne(function (StateInterface $state) {
            return $state->isInitial();
        });
        $reachableStates = $this->depthFirstScan($states, $initialState, new StateSet);
        if (count($reachableStates) !== count($states)) {
            throw new InvalidStructure('Not all states are properly connected.');
        }
    }

    public function has(string $stateName): bool
    {
        return $this->internalMap->hasKey($stateName);
    }

    public function get(string $stateName): TransitionSet
    {
        return $this->internalMap->get($stateName, new TransitionSet);
    }

    public function count(): int
    {
        return $this->internalMap->count();
    }

    public function getIterator(): Traversable
    {
        return $this->internalMap->getIterator();
    }

    public function toArray()
    {
        return $this->internalMap->toArray();
    }

    private function depthFirstScan(StateMap $states, StateInterface $state, StateSet $visitedStates): StateSet
    {
        if ($visitedStates->contains($state)) {
            return $visitedStates;
        }
        $visitedStates->add($state);
        $childStates = array_map(
            function (TransitionInterface $transition) use ($states): StateInterface {
                return $states->get($transition->getTo());
            },
            $this->get($state->getName())->toArray()
        );
        foreach ($childStates as $childState) {
            $visitedStates = $this->depthFirstScan($states, $childState, $visitedStates);
        }
        return $visitedStates;
    }
}
