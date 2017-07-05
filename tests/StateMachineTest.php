<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests;

use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Param\Settings;
use Daikon\StateMachine\State\FinalState;
use Daikon\StateMachine\State\InitialState;
use Daikon\StateMachine\State\InteractiveState;
use Daikon\StateMachine\State\StateSet;
use Daikon\StateMachine\stateMachine;
use Daikon\StateMachine\Tests\Fixture\InactiveTransition;
use Daikon\StateMachine\Transition\ExpressionConstraint;
use Daikon\StateMachine\Transition\Transition;
use Daikon\StateMachine\Transition\TransitionSet;
use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class StateMachineTest extends TestCase
{
    public function testExecute()
    {
        $schema = new Schema(
            'default_schema',
            [ 'type' => 'assoc', 'properties' => [ 'is_ready' => [ 'type' => 'bool' ] ] ],
            new Factory
        );
        $states = new StateSet([
            $this->createState('initial', InitialState::CLASS, null, $schema),
            $this->createState('foobar'),
            $this->createState('bar', InteractiveState::CLASS),
            $this->createState('final', FinalState::CLASS)
        ]);
        $transitions = (new TransitionSet)
            ->add(new Transition(
                'initial',
                'foobar',
                new Settings,
                [ new ExpressionConstraint('input.get("is_ready") == true', new ExpressionLanguage) ]
            ))
            ->add(new Transition('foobar', 'bar'))
            ->add(new Transition('bar', 'final'));
        $stateMachine = new StateMachine('test-machine', $states, $transitions);
        $intialOutput = $stateMachine->execute(new Input([ 'is_ready' => true ]), 'initial');
        $input = Input::fromOutput($intialOutput)->withEvent('on_signal');
        $output = $stateMachine->execute($input, $intialOutput->getCurrentState());
        $this->assertEquals('final', $output->getCurrentState());
    }

    public function testGetName()
    {
        $stateMachine = $this->buildstateMachine();
        $this->assertEquals('test-machine', $stateMachine->getName());
    }

    public function testGetInitialState()
    {
        $stateMachine = $this->buildstateMachine();
        $this->assertEquals('initial', $stateMachine->getInitialState()->getName());
    }

    public function testGetStates()
    {
        $stateMachine = $this->buildstateMachine();
        $this->assertCount(6, $stateMachine->getStates());
    }

    public function testFinalStates()
    {
        $stateMachine = $this->buildstateMachine();
        $this->assertCount(1, $stateMachine->getFinalStates());
    }

    public function testGetStateTransitions()
    {
        $stateMachine = $this->buildstateMachine();
        $this->assertCount(5, $stateMachine->getStateTransitions());
    }

    /**
     * @expectedException Daikon\StateMachine\Error\ExecutionError
     */
    public function testMultipleActivatedTransitions()
    {
        $this->expectExceptionMessage('Trying to activate more than one transition at a time. '.
            'Transition: approval -> published was activated first. Now approval -> archive is being activated too.');

        $states = new StateSet([
            $this->createState('initial', InitialState::CLASS),
            $this->createState('edit'),
            $this->createState('approval'),
            $this->createState('published'),
            $this->createState('archive'),
            $this->createState('final', FinalState::CLASS)
        ]);
        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'edit'))
            ->add(new Transition('edit', 'approval'))
            ->add(new Transition('approval', 'published'))
            ->add(new Transition('approval', 'archive'))
            ->add(new Transition('published', 'archive'))
            ->add(new Transition('archive', 'final'));
        $stateMachine = new StateMachine('test-machine', $states, $transitions);
        $stateMachine->execute(new Input);
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\CorruptExecutionFlow
     */
    public function testInfiniteExecutionLoop()
    {
        $this->expectExceptionMessage('Trying to execute more than the allowed number of 20 workflow steps.
Looks like there is a loop between: approval -> published -> archive');

        $states = new StateSet([
            $this->createState('initial', InitialState::CLASS),
            $this->createState('edit'),
            $this->createState('approval'),
            $this->createState('published'),
            $this->createState('archive'),
            $this->createState('final', FinalState::CLASS)
        ]);
        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'edit'))
            ->add(new Transition('edit', 'approval'))
            ->add(new Transition('approval', 'published'))
            ->add(new Transition('published', 'archive'))
            ->add(new Transition('archive', 'approval'))
            ->add(new InactiveTransition('archive', 'final'));
        $stateMachine = new StateMachine('test-machine', $states, $transitions);
        $stateMachine->execute(new Input);
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ExecutionError
     * @expectedExceptionMessage Trying to (re)execute state-machine at final state: final
     */
    public function testResumeOnFinalState()
    {
        $stateMachine = $this->buildstateMachine();
        $stateMachine->execute(new Input, 'final');
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ExecutionError
     * @expectedExceptionMessage Trying to start state-machine execution at unknown state: baz
     */
    public function testResumeOnUnknownState()
    {
        $stateMachine = $this->buildstateMachine();
        $stateMachine->execute(new Input, 'baz');
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ExecutionError
     * @expectedExceptionMessage Trying to resume state-machine executing without providing an event/signal.
     */
    public function testResumeWithoutEvent()
    {
        $stateMachine = $this->buildstateMachine();
        $output = $stateMachine->execute(new Input);
        $stateMachine->execute(Input::fromOutput($output), $output->getCurrentState());
    } // @codeCoverageIgnore

    private function buildstateMachine()
    {
        $states = new StateSet([
            $this->createState('initial', InitialState::CLASS),
            $this->createState('edit'),
            $this->createState('approval', InteractiveState::CLASS),
            $this->createState('published'),
            $this->createState('archive'),
            $this->createState('final', FinalState::CLASS)
        ]);
        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'edit'))
            ->add(new Transition('edit', 'approval'))
            ->add(new Transition('approval', 'published'))
            ->add(new Transition('published', 'archive'))
            ->add(new Transition('archive', 'final'));
        return new StateMachine('test-machine', $states, $transitions);
    }
}
