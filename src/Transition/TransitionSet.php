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
use Ds\Set;
use IteratorAggregate;
use Traversable;
use Daikon\StateMachine\Transition\TransitionInterface;

final class TransitionSet implements IteratorAggregate, Countable
{
    private $internal_set;

    public function __construct(array $transitions = [])
    {
        $this->internal_set = new Set(
            (function (TransitionInterface ...$transitions) {
                return $transitions;
            })(...$transitions)
        );
    }

    public function add(TransitionInterface $transition): self
    {
        $transitions = $this->internal_set->toArray();
        $transitions[] = $transition;

        return new static($transitions);
    }

    public function contains(TransitionInterface $transition): bool
    {
        foreach ($this->internal_set as $cur_transition) {
            if ($cur_transition->getFrom() === $transition->getFrom()
                && $cur_transition->getTo() === $transition->getTo()
            ) {
                return true;
            }
        }
        return false;
    }

    public function filter(callable $callback): self
    {
        $set = clone $this;
        $set->internal_set = $this->internal_set->filter($callback);

        return $set;
    }

    public function getIterator(): Traversable
    {
        return $this->internal_set->getIterator();
    }

    public function count(): int
    {
        return $this->internal_set->count();
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
