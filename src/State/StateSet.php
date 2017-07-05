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
use Ds\Set;
use IteratorAggregate;
use Traversable;
use Daikon\StateMachine\Error\InvalidStructure;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\StateMap;

final class StateSet implements IteratorAggregate, Countable
{
    private $internal_set;

    public function __construct(array $states = [])
    {
        $this->internal_set = new Set(
            (function (StateInterface ...$states) {
                return $states;
            })(...$states)
        );
    }

    public function splat(): array
    {
        $initial_state = null;
        $all_states = new StateMap;
        $final_states = new StateMap;
        foreach ($this->internal_set as $state) {
            if ($state->isFinal()) {
                if ($state->isInitial()) {
                    throw new InvalidStructure('Trying to add state as initial and final at the same time.');
                }
                $final_states = $final_states->put($state);
            }
            if ($state->isInitial()) {
                if ($initial_state !== null) {
                    throw new InvalidStructure('Trying to add more than one initial state.');
                }
                $initial_state = $state;
            }
            $all_states = $all_states->put($state);
        }
        if (!$initial_state) {
            throw new InvalidStructure('Trying to create statemachine without an initial state.');
        }
        if ($final_states->count() === 0) {
            throw new InvalidStructure('Trying to create statemachine without at least one final state.');
        }
        return [ $initial_state, $all_states, $final_states ];
    }

    public function add(StateInterface $state): self
    {
        $cloned_set = clone $this;
        $cloned_set->internal_set->add($state);

        return $cloned_set;
    }

    public function contains(StateInterface $state): bool
    {
        return $this->internal_set->contains($state);
    }

    public function count(): int
    {
        return $this->internal_set->count();
    }

    public function getIterator(): Traversable
    {
        return $this->internal_set->getIterator();
    }

    public function toArray(): array
    {
        return $this->internal_set->toArray();
    }

    public function __clone()
    {
        $this->internal_set = clone $this->internal_set;
    }
}
