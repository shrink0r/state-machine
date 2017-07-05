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
use Daikon\StateMachine\Tests\TestCase;

final class StateMapTest extends TestCase
{
    public function testCount()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $this->assertCount(5, $stateMap);
    }

    public function testPut()
    {
        $stateMap = (new StateMap)->put($this->createState('initial', InitialState::CLASS));
        $this->assertCount(1, $stateMap);
    }

    public function testGet()
    {
        $stateMap = new StateMap([
            $this->createState('initial', InitialState::CLASS),
            $foo_state = $this->createState('foo')
        ]);
        $this->assertEquals($foo_state, $stateMap->get('foo'));
    }

    public function testHas()
    {
        $stateMap = new StateMap([
            $this->createState('state1'),
            $this->createState('state2')
        ]);
        $this->assertTrue($stateMap->has('state1'));
        $this->assertFalse($stateMap->has('state3'));
    }

    public function testFind()
    {
        $stateMap = (new StateMap($this->buildStateArray()))->find(function (StateInterface $state) {
            return !$state->isFinal() && !$state->isInitial();
        });
        $this->assertCount(3, $stateMap);
    }

    public function testFindOne()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $barState = $stateMap->findOne(function (StateInterface $state) {
            return $state->getName() === 'bar';
        });
        $this->assertEquals($stateMap->get('bar'), $barState);
        $unknownState = $stateMap->findOne(function (StateInterface $state) {
            return $state->getName() === 'snafu';
        });
        $this->assertNull($unknownState);
    }

    public function testGetIterator()
    {
        $stateMap = new StateMap($this->buildStateArray());
        $i = 0;
        foreach ($stateMap as $stateName => $state) {
            $this->assertEquals($stateName, $state->getName());
            $i++;
        }
        $this->assertEquals(5, $i);
    }

    public function testToArray()
    {
        $states = $this->buildStateArray();
        $stateMap = new StateMap($states);
        $expectedArray = array_combine(
            array_map(function (StateInterface $state) {
                return $state->getName();
            }, $states),
            $states
        );
        $this->assertEquals($expectedArray, $stateMap->toArray());
    }

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
