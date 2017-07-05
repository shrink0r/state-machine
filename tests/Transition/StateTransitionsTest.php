<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Transition;

use Daikon\StateMachine\State\FinalState;
use Daikon\StateMachine\State\InitialState;
use Daikon\StateMachine\State\StateMap;
use Daikon\StateMachine\Tests\TestCase;
use Daikon\StateMachine\Transition\StateTransitions;
use Daikon\StateMachine\Transition\Transition;
use Daikon\StateMachine\Transition\TransitionSet;

final class StateTransitionsTest extends TestCase
{
    public function testCount()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $transitionSet = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        $stateTransitions = new StateTransitions($stateMap, $transitionSet);
        $this->assertCount(count($transitionSet), $stateTransitions);
    }

    public function testHas()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $transitionSet = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        $state_transitions = new StateTransitions($stateMap, $transitionSet);
        $this->assertTrue($state_transitions->has('initial'));
        $this->assertFalse($state_transitions->has('baz'));
    }

    public function testToArray()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $transitionSet = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        $stateTransitions = new StateTransitions($stateMap, $transitionSet);
        $expectedTransitionSets = [ 'initial', 'foo', 'bar', 'foobar' ];
        $stateTransitionsArray = $stateTransitions->toArray();
        foreach ($expectedTransitionSets as $expectedTransitionSet) {
            $this->assertInstanceOf(TransitionSet::CLASS, $stateTransitionsArray[$expectedTransitionSet]);
        }
    }

    /**
     * @expectedException \Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Trying to transition to unknown state: foobaz
     */
    public function testNonExistantToState()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $transitionSet = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobaz'),
            new Transition('foobar', 'final')
        ]);
        new StateTransitions($stateMap, $transitionSet);
    } // @codeCoverageIgnore

    /**
     * @expectedException \Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Trying to transition from unknown state: fu
     */
    public function testNonExistantFromState()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $transitionSet = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('fu', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        new StateTransitions($stateMap, $transitionSet);
    } // @codeCoverageIgnore

    /**
     * @expectedException \Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Trying to transition to initial-state: initial
     */
    public function testTransitionToInitialState()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $transitionSet = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'initial'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final')
        ]);
        new StateTransitions($stateMap, $transitionSet);
    } // @codeCoverageIgnore

    /**
     * @expectedException \Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Trying to transition from final-state: final
     */
    public function testTransitionFromFinalState()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $transitionSet = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar'),
            new Transition('foobar', 'final'),
            new Transition('final', 'foo')
        ]);
        new StateTransitions($stateMap, $transitionSet);
    } // @codeCoverageIgnore

    /**
     * @expectedException \Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Not all states are properly connected.
     */
    public function testStatesNotConnected()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $transitionSet = new TransitionSet([
            new Transition('initial', 'foo'),
            new Transition('foo', 'bar'),
            new Transition('bar', 'foobar')
        ]);
        new StateTransitions($stateMap, $transitionSet);
    } // @codeCoverageIgnore

    private function buildStateArray()
    {
        return [
            $this->createState('initial', InitialState::CLASS),
            $this->createState('foo'),
            $this->createState('bar'),
            $this->createState('foobar'),
            $this->createState('final', FinalState::CLASS)
        ];
    }
}
