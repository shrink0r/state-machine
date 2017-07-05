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
        $transitionSet = new TransitionSet($this->buildTransitionArray());
        $this->assertCount(4, $transitionSet);
    }

    public function testAdd()
    {
        $transitionSet = (new TransitionSet)->add(new Transition('state1', 'state2'));
        $this->assertCount(1, $transitionSet);
    }

    public function testContains()
    {
        $transitionSet = new TransitionSet($this->buildTransitionArray());
        $this->assertTrue($transitionSet->contains(new Transition('foo', 'bar')));
        $this->assertFalse($transitionSet->contains(new Transition('bar', 'foo')));
    }

    public function testFilter()
    {
        $transitionSet = (new TransitionSet($this->buildTransitionArray()))
            ->filter(function (TransitionInterface $transition) {
                return $transition->getTo() === 'bar';
            });
        $barTransition = $transitionSet->toArray()[0];
        $this->assertCount(1, $transitionSet);
        $this->assertEquals('foo', $barTransition->getFrom());
        $this->assertEquals('bar', $barTransition->getTo());
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
