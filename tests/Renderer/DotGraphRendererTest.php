<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Renderer;

use Daikon\StateMachine\Param\Settings;
use Daikon\StateMachine\Renderer\DotGraphRenderer;
use Daikon\StateMachine\StateMachine;
use Daikon\StateMachine\State\InteractiveState;
use Daikon\StateMachine\State\FinalState;
use Daikon\StateMachine\State\InitialState;
use Daikon\StateMachine\State\State;
use Daikon\StateMachine\State\StateSet;
use Daikon\StateMachine\Tests\TestCase;
use Daikon\StateMachine\Transition\Transition;
use Daikon\StateMachine\Transition\TransitionSet;

final class DotGraphRendererTest extends TestCase
{
    public function testRenderer()
    {
        $states = new StateSet([
            $this->createState('initial', InitialState::CLASS),
            $this->createState('foobar'),
            $this->createState('bar'),
            $this->createState('final', FinalState::CLASS)
        ]);

        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'foobar', new Settings))
            ->add(new Transition('foobar', 'bar', new Settings))
            ->add(new Transition('bar', 'final', new Settings));

        $state_machine = new StateMachine('test-machine', $states, $transitions);
        $expected_graph = file_get_contents(__DIR__ . '/Fixture/testcase_1.dot');

        $renderer = new DotGraphRenderer;
        $this->assertEquals($expected_graph, $renderer->render($state_machine));
    }
}
