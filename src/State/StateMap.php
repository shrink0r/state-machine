<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\State;

use Closure;
use Countable;
use Ds\Map;
use IteratorAggregate;
use Traversable;

final class StateMap implements IteratorAggregate, Countable
{
    private $internalMap;

    public function __construct(array $states = [])
    {
        $this->internalMap = new Map;
        (function (StateInterface ...$states) {
            foreach ($states as $state) {
                 $this->internalMap->put($state->getName(), $state);
            }
        })(...$states);
    }

    public function put(StateInterface $state): self
    {
        $clonedMap = clone $this;
        $clonedMap->internalMap->put($state->getName(), $state);
        return $clonedMap;
    }

    public function find(Closure $query): self
    {
        $states = [];
        foreach ($this->internalMap as $state) {
            if (true === $query($state)) {
                $states[] = $state;
            }
        }
        return new self($states);
    }

    public function findOne(Closure $query)
    {
        foreach ($this->internalMap as $state) {
            if (true === $query($state)) {
                return $state;
            }
        }
        return null;
    }

    public function has(string $stateName): bool
    {
        return $this->internalMap->hasKey($stateName);
    }

    public function get(string $stateName): StateInterface
    {
        return $this->internalMap->get($stateName);
    }

    public function count(): int
    {
        return $this->internalMap->count();
    }

    public function getIterator(): Traversable
    {
        return $this->internalMap->getIterator();
    }

    public function toArray(): array
    {
        return $this->internalMap->toArray();
    }

    public function __clone()
    {
        $this->internalMap = clone $this->internalMap;
    }
}
