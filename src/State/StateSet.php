<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Countable;
use Daikon\StateMachine\Error\InvalidStructure;
use Ds\Set;
use IteratorAggregate;
use Traversable;

final class StateSet implements IteratorAggregate, Countable
{
    private $internalSet;

    public function __construct(array $states = [])
    {
        $this->internalSet = new Set(
            (function (StateInterface ...$states) {
                return $states;
            })(...$states)
        );
    }

    public function splat(): array
    {
        $initialState = null;
        $allStates = new StateMap;
        $finalStates = new StateMap;
        foreach ($this->internalSet as $state) {
            if ($state->isFinal()) {
                if ($state->isInitial()) {
                    throw new InvalidStructure('Trying to add state as initial and final at the same time.');
                }
                $finalStates = $finalStates->put($state);
            }
            if ($state->isInitial()) {
                if ($initialState !== null) {
                    throw new InvalidStructure('Trying to add more than one initial state.');
                }
                $initialState = $state;
            }
            $allStates = $allStates->put($state);
        }
        if (!$initialState) {
            throw new InvalidStructure('Trying to create state-machine without an initial state.');
        }
        if ($finalStates->count() === 0) {
            throw new InvalidStructure('Trying to create state-machine without at least one final state.');
        }
        return [ $initialState, $allStates, $finalStates ];
    }

    public function add(StateInterface $state): self
    {
        $clonedSet = clone $this;
        $clonedSet->internalSet->add($state);

        return $clonedSet;
    }

    public function contains(StateInterface $state): bool
    {
        return $this->internalSet->contains($state);
    }

    public function count(): int
    {
        return $this->internalSet->count();
    }

    public function getIterator(): Traversable
    {
        return $this->internalSet->getIterator();
    }

    public function toArray(): array
    {
        return $this->internalSet->toArray();
    }

    public function __clone()
    {
        $this->internalSet = clone $this->internalSet;
    }
}
