<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Builder;

use Daikon\StateMachine\Builder\StateMachineBuilder;
use Daikon\StateMachine\Param\Settings;
use Daikon\StateMachine\StateMachine;
use Daikon\StateMachine\StateMachineInterface;
use Daikon\StateMachine\State\FinalState;
use Daikon\StateMachine\State\InitialState;
use Daikon\StateMachine\State\InteractiveState;
use Daikon\StateMachine\State\State;
use Daikon\StateMachine\Tests\Builder\Fixture\EmptyClass;
use Daikon\StateMachine\Tests\TestCase;
use Daikon\StateMachine\Transition\Transition;

final class StateMachineBuilderTest extends TestCase
{
    public function testBuild()
    {
        $state_machine = (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName('video-transcoding')
            ->addState($this->createState('initial', InitialState::CLASS))
            ->addStates([
                $this->createState('state1', InteractiveState::CLASS),
                $this->createState('state2'),
                $this->createState('final', FinalState::CLASS)
            ])
            ->addTransition(new Transition('initial', 'state1'))
            ->addTransitions([
                new Transition('state1', 'state2'),
                new Transition('state2', 'final')
            ])
            ->build();
        $this->assertInstanceOf(StateMachineInterface::CLASS, $state_machine);
        $this->assertEquals('video-transcoding', $state_machine->getName());
    }

    /**
     * @expectedException Daikon\StateMachine\Error\MissingImplementation
     */
    public function testMissingInterface()
    {
        new StateMachineBuilder(EmptyClass::CLASS);
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\MissingImplementation
     */
    public function testNonExistantClass()
    {
        new StateMachineBuilder('FooBarMachine');
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\UnknownState
     */
    public function testUnknownFromState()
    {
        (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName('video-transcoding')
            ->addState($this->createState('initial', InitialState::CLASS))
            ->addState($this->createState('state1'))
            ->addState($this->createState('final', FinalState::CLASS))
            ->addTransition(new Transition('start', 'state1'));
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\UnknownState
     */
    public function testUnknownToState()
    {
        (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName('video-transcoding')
            ->addState($this->createState('initial', InitialState::CLASS))
            ->addState($this->createState('state1'))
            ->addState($this->createState('final', FinalState::CLASS))
            ->addTransition(new Transition('state1', 'state2'));
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\InvalidStructure
     */
    public function testDuplicateTransition()
    {
        (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName('video-transcoding')
            ->addState($this->createState('initial', InitialState::CLASS))
            ->addState($this->createState('state1'))
            ->addState($this->createState('final', FinalState::CLASS))
            ->addTransition(new Transition('initial', 'state1'))
            ->addTransition(new Transition('initial', 'state1'));
    } // @codeCoverageIgnore
}
