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
        $state_set = new StateSet($this->buildStateArray());
        $this->assertCount(5, $state_set);
    }

    public function testSplat()
    {
        $state_set = new StateSet($this->buildStateArray());
        list($initial_state, $all_states, $final_states) = $state_set->splat();
        $this->assertInstanceOf(StateInterface::CLASS, $initial_state);
        $this->assertTrue($initial_state->isInitial());
        $this->assertCount(5, $all_states);
        $this->assertInstanceOf(StateMap::CLASS, $all_states);
        $this->assertCount(1, $final_states);
        $this->assertInstanceOf(StateMap::CLASS, $final_states);
        $this->assertTrue($final_states->get('final')->isFinal());
    }

    public function testGetIterator()
    {
        $states_array = $this->buildStateArray();
        $state_set = new StateSet($states_array);
        $i = 0;
        foreach ($state_set as $state) {
            $this->assertEquals($states_array[$i], $state);
            $i++;
        }
        $this->assertEquals(count($states_array), $i);
    }

    public function testToArray()
    {
        $states_array = $this->buildStateArray();
        $state_set = new StateSet($states_array);
        $this->assertEquals($states_array, $state_set->toArray());
    }

    /**
     * @expectedException Daikon\StateMachine\Error\InvalidStructure
     * @expectedExceptionMessage Trying to add more than one initial state.
     */
    public function testMultipleInitialStates()
    {
        $states_array = $this->buildStateArray();
        $states_array[] = $this->createState('snafu', InitialState::CLASS);
        $state_set = new StateSet($states_array);
        $state_set->splat();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\InvalidStructure
     * @expectedExceptionMessage Trying to add state as initial and final at the same time.
     */
    public function testInconsistentType()
    {
        $states_array = $this->buildStateArray();
        $states_array[] = $this->createState('snafu', TwoFaceState::CLASS);
        $state_set = new StateSet($states_array);
        $state_set->splat();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\InvalidStructure
     * @expectedExceptionMessage Trying to create statemachine without an initial state.
     */
    public function testMissingInitialState()
    {
        $states_array = $this->buildStateArray();
        array_shift($states_array);
        $state_set = new StateSet($states_array);
        $state_set->splat();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\InvalidStructure
     * @expectedExceptionMessage Trying to create statemachine without at least one final state.
     */
    public function testMissingFinalState()
    {
        $states_array = $this->buildStateArray();
        array_pop($states_array);
        $state_set = new StateSet($states_array);
        $state_set->splat();
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
