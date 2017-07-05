<?php
/**
 * This file is part of the daikon-cqrs/state-machine project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\StateMachine\Tests\Builder;

use Daikon\StateMachine\Builder\ArrayStateMachineBuilder;
use Daikon\StateMachine\Param\Input;
use Daikon\StateMachine\Tests\TestCase;
use Symfony\Component\Yaml\Parser;

final class ArrayStateMachineBuilderTest extends TestCase
{
    public function testBuild()
    {
        $state_machine = (new ArrayStateMachineBuilder($this->fixture('state_machine')))->build();

        $initialInput = new Input([ 'transcoding_required' => true ]);
        $initialOutput = $state_machine->execute($initialInput);
        $currentState = $initialOutput->getCurrentState();
        $this->assertEquals('transcoding', $currentState);
        $input = Input::fromOutput($initialOutput)->withEvent('video_transcoded');
        $finalOutput = $state_machine->execute($input, $currentState);
        $this->assertEquals('ready', $finalOutput->getCurrentState());
    }

    public function testNonStringConstraint()
    {
        (new ArrayStateMachineBuilder($this->fixture('non_string_constraint')))->build();
    }

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     */
    public function testEmptyConfig()
    {
        (new ArrayStateMachineBuilder([]))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     */
    public function testInvalidStateMachineSchema()
    {
        (new ArrayStateMachineBuilder($this->fixture('invalid_schema')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     * @expectedExceptionMessage
        Trying to provide custom state that isn't initial but marked as initial in config.
     */
    public function testInconsistentInitialState()
    {
        (new ArrayStateMachineBuilder($this->fixture('inconsistent_initial')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     * @expectedExceptionMessage
        Trying to provide custom state that isn't interactive but marked as interactive in config.
     */
    public function testInconsistentInteractiveState()
    {
        (new ArrayStateMachineBuilder($this->fixture('inconsistent_interactive')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\ConfigError
     * @expectedExceptionMessage
        Trying to provide custom state that isn't final but marked as final in config.
     */
    public function testInconsistentFinalState()
    {
        (new ArrayStateMachineBuilder($this->fixture('inconsistent_final')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\MissingImplementation
     */
    public function testNonImplementedState()
    {
        (new ArrayStateMachineBuilder($this->fixture('non_implemented_state')))->build();
    } // @codeCoverageIgnore

    /**
     * @expectedException Daikon\StateMachine\Error\MissingImplementation
     */
    public function testNonImplementedTransition()
    {
        (new ArrayStateMachineBuilder($this->fixture('non_implemented_transition')))->build();
    } // @codeCoverageIgnore

    private function fixture(string $name): array
    {
        return (new Parser)->parse(file_get_contents(__DIR__.'/Fixture/Yaml/'.$name.'.yaml'));
    }
}
