<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\State;

use Daikon\StateMachine\State\FinalState;
use Daikon\StateMachine\State\InitialState;
use Daikon\StateMachine\State\StateInterface;
use Daikon\StateMachine\State\StateMap;
use Daikon\StateMachine\State\StateSet;
use Daikon\StateMachine\Tests\State\Fixture\TwoFaceState;
use Daikon\StateMachine\Tests\TestCase;

final class StateSetTest extends TestCase
{
    public function testCount()
    {
        $stateSet = new StateSet($this->buildStateArray());
        $this->assertCount(5, $stateSet);
    }

    public function testSplat()
    {
        $stateSet = new StateSet($this->buildStateArray());
        list($initialState, $allStates, $finalStates) = $stateSet->splat();
        $this->assertInstanceOf(StateInterface::CLASS, $initialState);
        $this->assertTrue($initialState->isInitial());
        $this->assertCount(5, $allStates);
        $this->assertInstanceOf(StateMap::CLASS, $allStates);
        $this->assertCount(1, $finalStates);
        $this->assertInstanceOf(StateMap::CLASS, $finalStates);
        $this->assertTrue($finalStates->get('final')->isFinal());
    }

    public function testGetIterator()
    {
        $statesArray = $this->buildStateArray();
        $stateSet = new StateSet($statesArray);
        $i = 0;
        foreach ($stateSet as $state) {
            $this->assertEquals($statesArray[$i], $state);
            $i++;
        }
        $this->assertEquals(count($statesArray), $i);
    }

    public function testToArray()
    {
        $statesArray = $this->buildStateArray();
        $stateSet = new StateSet($statesArray);
        $this->assertEquals($statesArray, $stateSet->toArray());
    }

    /**
     * @expectedException Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Trying to add more than one initial state.
     */
    public function testMultipleInitialStates()
    {
        $statesArray = $this->buildStateArray();
        $statesArray[] = $this->createState('snafu', InitialState::CLASS);
        $stateSet = new StateSet($statesArray);
        $stateSet->splat();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Trying to add state as initial and final at the same time.
     */
    public function testInconsistentType()
    {
        $statesArray = $this->buildStateArray();
        $statesArray[] = $this->createState('snafu', TwoFaceState::CLASS);
        $stateSet = new StateSet($statesArray);
        $stateSet->splat();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Trying to create state-machine without an initial state.
     */
    public function testMissingInitialState()
    {
        $statesArray = $this->buildStateArray();
        array_shift($statesArray);
        $stateSet = new StateSet($statesArray);
        $stateSet->splat();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Exception\InvalidStructure
     * @expectedExceptionMessage Trying to create state-machine without at least one final state.
     */
    public function testMissingFinalState()
    {
        $statesArray = $this->buildStateArray();
        array_pop($statesArray);
        $stateSet = new StateSet($statesArray);
        $stateSet->splat();
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
