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
use Daikon\StateMachine\State\StateInterface;

final class StateMap implements IteratorAggregate, Countable
{
    private $internal_map;

    public function __construct(array $states = [])
    {
        $this->internal_map = new Map;
        (function (StateInterface ...$states) {
            foreach ($states as $state) {
                 $this->internal_map->put($state->getName(), $state);
            }
        })(...$states);
    }

    public function put(StateInterface $state): self
    {
        $cloned_map = clone $this;
        $cloned_map->internal_map->put($state->getName(), $state);
        return $cloned_map;
    }

    public function find(Closure $query): self
    {
        $states = [];
        foreach ($this->internal_map as $state) {
            if (true === $query($state)) {
                $states[] = $state;
            }
        }
        return new self($states);
    }

    public function findOne(Closure $query)
    {
        foreach ($this->internal_map as $state) {
            if (true === $query($state)) {
                return $state;
            }
        }
        return null;
    }

    public function has(string $state_name): bool
    {
        return $this->internal_map->hasKey($state_name);
    }

    public function get(string $state_name): StateInterface
    {
        return $this->internal_map->get($state_name);
    }

    public function count(): int
    {
        return $this->internal_map->count();
    }

    public function getIterator(): Traversable
    {
        return $this->internal_map->getIterator();
    }

    public function toArray(): array
    {
        return $this->internal_map->toArray();
    }

    public function __clone()
    {
        $this->internal_map = clone $this->internal_map;
    }
}
