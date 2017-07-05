<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Transition;

use Daikon\StateMachine\Tests\TestCase;
use Daikon\StateMachine\Transition\Transition;
use Daikon\StateMachine\Transition\TransitionInterface;
use Daikon\StateMachine\Transition\TransitionSet;

final class TransitionSetTest extends TestCase
{
    public function testCount()
    {
        $transition_set = new TransitionSet($this->buildTransitionArray());
        $this->assertCount(4, $transition_set);
    }

    public function testAdd()
    {
        $transition_set = (new TransitionSet)->add(new Transition('state1', 'state2'));
        $this->assertCount(1, $transition_set);
    }

    public function testContains()
    {
        $transition_set = new TransitionSet($this->buildTransitionArray());
        $this->assertTrue($transition_set->contains(new Transition('foo', 'bar')));
        $this->assertFalse($transition_set->contains(new Transition('bar', 'foo')));
    }

    public function testFilter()
    {
        $transition_set = (new TransitionSet($this->buildTransitionArray()))
            ->filter(function (TransitionInterface $transition) {
                return $transition->getTo() === 'bar';
            });
        $bar_transition = $transition_set->toArray()[0];
        $this->assertCount(1, $transition_set);
        $this->assertEquals('foo', $bar_transition->getFrom());
        $this->assertEquals('bar', $bar_transition->getTo());
    }

    private function buildTransitionArray()
    {
        return [
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ];
    }
}
