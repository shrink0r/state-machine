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

final class TransitionSet implements IteratorAggregate, Countable
{
    private $internalSet;

    public function __construct(array $transitions = [])
    {
        $this->internalSet = new Set(
            (function (TransitionInterface ...$transitions) {
                return $transitions;
            })(...$transitions)
        );
    }

    public function add(TransitionInterface $transition): self
    {
        $transitions = $this->internalSet->toArray();
        $transitions[] = $transition;

        return new static($transitions);
    }

    public function contains(TransitionInterface $transition): bool
    {
        foreach ($this->internalSet as $curTransition) {
            if ($curTransition->getFrom() === $transition->getFrom()
                && $curTransition->getTo() === $transition->getTo()
            ) {
                return true;
            }
        }
        return false;
    }

    public function filter(callable $callback): self
    {
        $set = clone $this;
        $set->internalSet = $this->internalSet->filter($callback);

        return $set;
    }

    public function getIterator(): Traversable
    {
        return $this->internalSet->getIterator();
    }

    public function count(): int
    {
        return $this->internalSet->count();
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
